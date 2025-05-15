<?php

declare(strict_types=1);

namespace Core\Symfony\DependencyInjection;

use Core\Pathfinder\Path;
use JetBrains\PhpStorm\Language;
use Support\Time;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\{ContainerBuilder, Definition, Reference};
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\DependencyInjection\Loader\Configurator\ReferenceConfigurator;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Yaml\Yaml;
use UnexpectedValueException;
use function Support\normalize_path;

/**
 * Compiler pass abstraction layer for handling config files.
 */
abstract class CompilerPass implements CompilerPassInterface
{
    protected readonly string $projectDirectory;

    protected readonly ContainerBuilder $container;

    protected readonly SymfonyStyle $console;

    protected readonly ParameterBagInterface $parameterBag;

    abstract public function compile( ContainerBuilder $container ) : void;

    public static function placeholder( mixed $type ) : string
    {
        return \gettype( $type );
    }

    final public function process( ContainerBuilder $container ) : void
    {
        $this->container        = $container;
        $this->console          = new SymfonyStyle( new StringInput( '' ), new ConsoleOutput() );
        $this->parameterBag     = $container->getParameterBag();
        $this->projectDirectory = $this->setProjectDirectory();

        $this->compile( $container );
    }

    /**
     * @param Reference|ReferenceConfigurator|string $id
     * @param false|string                           $newOnMissing
     * @param bool                                   $nullable
     *
     * @return ($nullable is true ? null|Definition : Definition)
     */
    final protected function getDefinition(
        string|ReferenceConfigurator|Reference $id,
        false|string                           $newOnMissing = false,
        bool                                   $nullable = false,
    ) : ?Definition {
        $id = \is_string( $id ) ? $id : $id->__toString();

        $hasDefinition = $this->container->hasDefinition( $id );

        if ( $hasDefinition ) {
            return $this->container->getDefinition( $id );
        }

        if ( $newOnMissing ) {
            return new Definition( $newOnMissing );
        }

        if ( $nullable ) {
            return null;
        }

        throw new ServiceNotFoundException(
            id  : $id,
            msg : $this::class." cannot find required '{$id}' definition.",
        );
    }

    final protected function getParameterPath( string $key, ?string $append = null ) : string
    {
        $path = $this->parameterBag->get( $key );
        if ( \is_string( $path ) ) {
            if ( $append ) {
                $path .= "/{$append}";
            }

            return normalize_path( $path );
        }

        $message = __METHOD__." {$key} returned ".\gettype( $path ).' from the ParameterBag.';

        throw new UnexpectedValueException( $message );
    }

    /**
     * @param string ...$tag
     *
     * @return string[]
     */
    final protected function taggedServiceIds( string ...$tag ) : array
    {
        $serviceIds = [];

        foreach ( $tag as $name ) {
            $serviceIds = [
                ...$serviceIds,
                ...$this->container->findTaggedServiceIds( $name ),
            ];
        }

        return \array_keys( $serviceIds );
    }

    /**
     * @param ?string $subclassOf
     * @param bool    $hasDefinition
     *
     * @return class-string[]
     */
    final protected function getDeclaredClasses(
        ?string $subclassOf = null,
        bool    $hasDefinition = false,
    ) : array {
        $declaredClasses = \array_values(
            \array_unique(
                [
                    ...\get_declared_classes(),
                    ...\array_filter(
                        $this->container->getServiceIds(),
                        static fn( $class ) => \class_exists( $class, false ),
                    ),
                ],
            ),
        );

        if ( $subclassOf ) {
            $declaredClasses = \array_filter(
                $declaredClasses,
                fn( $class ) => \is_subclass_of( $class, $subclassOf ),
            );
        }

        if ( $hasDefinition ) {
            $declaredClasses = \array_filter(
                $declaredClasses,
                [$this->container, 'hasDefinition'],
            );
        }

        return $declaredClasses;
    }

    protected function path( string $fromProjectDir ) : Path
    {
        return new Path( "{$this->projectDirectory}/{$fromProjectDir}" );
    }

    /**
     * @param string                         $fromProjectDir
     * @param array<array-key, mixed>|string $data
     * @param bool                           $override
     *
     * @return void
     */
    final protected function createYamlFile(
        string       $fromProjectDir,
        #[Language( 'PHP' )]
        string|array $data,
        bool         $override = false,
    ) : void {
        $path = $this->path( $fromProjectDir );

        if ( $path->exists() && $override === false ) {
            return;
        }

        $path->save( Yaml::dump( $data ) );
    }

    final protected function createPhpFile(
        string    $fromProjectDir,
        #[Language( 'PHP' )]
        string    $php,
        bool      $override = false,
        string ...$comment,
    ) : void {
        $path = $this->path( $fromProjectDir );

        if ( $path->exists() && $override === false ) {
            return;
        }

        $path->save( $this->parsePhpString( $php, ...$comment ) );
    }

    private function setProjectDirectory() : string
    {
        $projectDirectory = $this->parameterBag->get( 'kernel.project_dir' );

        \assert(
            \is_string( $projectDirectory )
                && \is_dir( $projectDirectory )
                && \is_writable( $projectDirectory ),
        );

        return normalize_path( $projectDirectory );
    }

    private function parsePhpString(
        #[Language( 'PHP' )]
        string    $php,
        string ...$comment,
    ) : string {
        if ( ! \str_starts_with( $php, '<?php' ) ) {
            throw new UnexpectedValueException( __METHOD__.': The provided PHP string has no opening tag.' );
        }

        if ( \str_ends_with( $php, '?>' ) ) {
            throw new UnexpectedValueException( __METHOD__.': PHP strings must not end with a closing tag.' );
        }

        $generator = '    This file is autogenerated by '.$this::class.'.';
        $generated = '    Date: '.( new Time() )->datetime;

        $separator = \str_repeat( '-', \strlen( $generator ) );

        $header   = [];
        $header[] = "\n\n/*{$separator}\n";
        $header[] = $generator;
        $header[] = $generated;
        if ( $comment ) {
            $header[] = '';

            foreach ( $comment as $line ) {
                $header[] = '    '.$line;
            }
        }
        $header[] = "\n{$separator}*/\n\n";

        $content = \preg_replace(
            pattern     : '#<\?php\s+?(?=\S)#A',
            replacement : '<?php'.\implode( "\n", $header ),
            subject     : (string) \preg_replace( '#^\h+$#m', '', $php ),
        );

        \assert( \is_string( $content ) );

        return $content;
    }
}
