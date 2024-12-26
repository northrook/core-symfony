<?php

namespace Core\Symfony\DependencyInjection;

use InvalidArgumentException;
use ReflectionClass;
use Symfony\Component\DependencyInjection\{Attribute\Autoconfigure,
    Attribute\Lazy,
    ContainerBuilder,
    Definition,
    Exception\AutoconfigureFailedException
};
use Symfony\Component\Finder\Finder;
use ReflectionAttribute;
use LogicException;
use Throwable;

final class AutodiscoverServicesPass extends CompilerPass
{
    /** @var array<class-string, class-string> */
    protected array $classMap = [];

    /** @var Autodiscover[] */
    protected array $autodiscover = [];

    public function compile( ContainerBuilder $container ) : void
    {
        $this->autodiscoverAnnotatedClasses();

        foreach ( $this->autodiscover as $className => $autoconfigure ) {
            $serviceId = $autoconfigure->setClassName( $className );

            if ( $container->hasDefinition( $serviceId ) ) {
                $definition = $container->getDefinition( $serviceId );
            }
            else {
                $definition = new Definition( $className );
            }

            $container->setDefinition( $serviceId, $definition );
        }
    }

    private function autodiscoverAnnotatedClasses() : void
    {
        $discover = new Finder();

        $discover->files();

        $discover->in( "{$this->projectDirectory}/src" );

        $discover->in( "{$this->projectDirectory}/vendor" )
            ->exclude(
                [
                    '*',
                    'bin',
                    'composer',
                    'latte',
                    'monolog',
                    'psr',
                    'symfony',
                    'tempest',
                    'twig',
                ],
            );

        $discover->files()->name( '*.php' );

        $autodiscover = [];

        foreach ( $discover as $file ) {
            $this->parseAutodisoveredFile( $file->getPathname() );
        }
    }

    private function parseAutodisoveredFile( string $path ) : void
    {
        $stream = \fopen( $path, 'r' );

        if ( false === $stream ) {
            throw new InvalidArgumentException( 'Unable to open file for autodiscovery: '.$path );
        }

        $className = null;
        $namespace = null;

        while ( false !== ( $line = \fgets( $stream ) ) ) {
            $line = \trim( (string) \preg_replace( '/\s+/', ' ', $line ) );

            if ( \str_starts_with( $line, 'namespace ' ) ) {
                $namespace = \substr( $line, \strlen( 'namespace' ) );
                $namespace = \trim( $namespace, " \n\r\t\v\0;" );
            }

            if ( $this->lineContainsDefinition( $line, $className ) ) {
                break;
            }
        }

        \fclose( $stream );

        if ( ! $className ) {
            return;
        }

        $className = $namespace.'\\'.$className;

        if ( ! \class_exists( $className ) ) {
            // $this->console->warning( "Class {$className} does not exist." );
            return;
        }

        $this->classMap[$className] = $className;

        try {
            $reflection = new ReflectionClass( $className );
        }
        catch ( Throwable $exception ) {
            $this->console->error( "Reflection Error: {$exception->getMessage()}" );
            return;
        }

        $flags = ReflectionAttribute::IS_INSTANCEOF;

        $autodiscoverAttribute = $reflection->getAttributes( Autodiscover::class, $flags );

        if ( empty( $autodiscoverAttribute ) ) {
            return;
        }

        $autodiscoverAttribute = \array_pop( $autodiscoverAttribute );

        if ( $reflection->getAttributes( Autoconfigure::class, $flags ) ) {
            throw new LogicException( "#[Autodiscover] error for {$className}; cannot use #[Autoconfigure] as well." );
        }
        if ( $reflection->getAttributes( Lazy::class, $flags ) ) {
            throw new LogicException( "#[Autodiscover] error for {$className}; cannot use #[Lazy] as well." );
        }

        /** @var Autodiscover $autodiscover */
        $autodiscover = $autodiscoverAttribute->newInstance();

        $autodiscover->setClassName( $className );

        $this->autodiscover[$className] = $autodiscover;

        //
        // if ($autoconfigure && $lazy) {
        //     throw new AutoconfigureFailedException($class->name, 'Using both attributes #[Lazy] and #[Autoconfigure] on an argument is not allowed; use the "lazy" parameter of #[Autoconfigure] instead.');
        // }

        // foreach ( $attributes as $attribute ) {
        //     if ( ! \is_subclass_of( $attribute->getName(), Autodiscover::class ) ) {
        //         continue;
        //     }
        //
        //     $autodiscover = $attribute->newInstanceArgs();
        //
        //     dump( $attribute->getTarget(), $autodiscover );
        //
        //     $this->autodiscover[$className] = $autodiscover;
        // }
    }

    private function lineContainsDefinition( string $line, ?string &$className ) : bool
    {
        if ( ! \str_contains( $line, 'class ' ) ) {
            return false;
        }

        foreach ( [
            'final class ',
            'final readonly class ',
            'abstract class ',
            'abstract readonly class ',
            'readonly class ',
            'class ',
        ] as $type ) {
            if ( \str_starts_with( $line, $type ) ) {
                $classString = \substr( $line, \strlen( $type ) );

                $className = \strstr( $classString, ' ', true ) ?: $classString;

                if ( ! $className ) {
                    dump( [$line => $className] );
                }

                return true;
            }
        }

        return false;
    }
}
