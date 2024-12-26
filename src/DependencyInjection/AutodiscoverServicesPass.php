<?php

namespace Core\Symfony\DependencyInjection;

use InvalidArgumentException;
use ReflectionClass;
use ReflectionException;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Finder\Finder;

final class AutodiscoverServicesPass extends CompilerPass
{
    /** @var array<class-string, class-string> */
    protected array $classMap = [];

    /** @var Autodiscover[] */
    protected array $autodiscover = [];

    private function __construct()
    {
        $this->autodiscoverAnnotatedClasses();
    }

    public function compile( ContainerBuilder $container ) : void
    {
        dump(
            $this->classMap,
            $this->autodiscover,
        );
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
            $this->console->warning( "Class {$className} does not exist." );
            return;
        }

        $this->classMap[$className] = $className;

        try {
            $reflection = new ReflectionClass( $className );
        }
        catch ( ReflectionException $exception ) {
            $this->console->error( "Reflection Error: {$exception->getMessage()}" );
            return;
        }

        $attributes = $reflection->getAttributes();

        if ( ! empty( $attributes ) ) {
            dump( $attributes );
        }
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
