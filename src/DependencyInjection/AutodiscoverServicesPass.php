<?php

declare(strict_types=1);

namespace Core\Symfony\DependencyInjection;

use Symfony\Component\DependencyInjection\{ContainerBuilder, Definition};
use Symfony\Component\DependencyInjection\Attribute\{Autoconfigure, Lazy};
use Symfony\Component\Finder\Finder;
use ReflectionClass, ReflectionAttribute;
use InvalidArgumentException, LogicException, BadMethodCallException;

final class AutodiscoverServicesPass extends CompilerPass
{
    /** @var array<class-string, class-string> */
    protected array $classMap = [];

    /** @var Autodiscover[] */
    protected array $autodiscover = [];

    public function compile( ContainerBuilder $container ) : void
    {
        $this->autodiscoverAnnotatedClasses();

        foreach ( $this->autodiscover as $className => $config ) {
            $serviceId = $config->serviceID;

            if ( $container->hasDefinition( $serviceId ) ) {
                $definition = $container->getDefinition( $serviceId );
            }
            else {
                $definition = new Definition( $className );
            }

            if ( null !== $config->tags ) {
                foreach ( $config->tags as $key => $tag ) {
                    if ( \is_string( $tag ) ) {
                        $key = $tag;
                        $tag = [];
                    }
                    if ( \is_array( $tag ) ) {
                        \assert( \is_string( $key ) );
                    }
                    $definition->addTag( $key, $tag );
                }
            }

            if ( null !== $config->calls ) {
                $definition->setMethodCalls( $config->calls );
            }

            if ( null !== $config->bind ) {
                $definition->setBindings( $config->bind );
            }

            if ( null !== $config->lazy ) {
                $definition->setLazy( $config->lazy );
            }

            if ( null !== $config->public ) {
                $definition->setPublic( $config->public );
            }

            if ( null !== $config->shared ) {
                $definition->setShared( $config->shared );
            }

            if ( null !== $config->autowire ) {
                $definition->setAutowired( $config->autowire );
            }

            if ( null !== $config->properties ) {
                $definition->setProperties( $config->properties );
            }

            if ( null !== $config->configurator ) {
                $definition->setConfigurator( $config->configurator );
            }

            if ( null !== $config->constructor ) {
                // TODO: Autoconfigure::$config->constructor
                throw new BadMethodCallException( 'Autoconfigure::$config->constructor Not implemented' );
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
            return;
        }

        $this->classMap[$className] = $className;

        $reflection = new ReflectionClass( $className );
        $flags      = ReflectionAttribute::IS_INSTANCEOF;
        $attributes = $reflection->getAttributes( Autodiscover::class, $flags );

        if ( empty( $attributes ) ) {
            return;
        }

        $attributes = \array_pop( $attributes );

        if ( $reflection->getAttributes( Autoconfigure::class, $flags ) ) {
            throw new LogicException( "#[Autodiscover] error for {$className}; cannot use #[Autoconfigure] as well." );
        }
        if ( $reflection->getAttributes( Lazy::class, $flags ) ) {
            throw new LogicException( "#[Autodiscover] error for {$className}; cannot use #[Lazy] as well." );
        }

        /** @var Autodiscover $autodiscover */
        $autodiscover = $attributes->newInstance();

        $autodiscover->setClassName( $className );

        $this->autodiscover[$className] = $autodiscover;
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
                    $this->console->warning( 'Expected a valid class name for class '.$classString );
                }

                return true;
            }
        }

        return false;
    }
}
