<?php

declare(strict_types=1);

namespace Core\Symfony\Compiler;

use Core\Symfony\Console\ListReport;
use Core\Symfony\DependencyInjection\{Autodiscover, CompilerPass};
use Symfony\Component\DependencyInjection\{ContainerBuilder, Definition};
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Support\{ClassFinder, ClassInfo};
use LogicException, BadMethodCallException;
use ReflectionAttribute, ReflectionClass;

final class AutodiscoverServicesPass extends CompilerPass
{
    /** @var array<class-string, class-string> */
    protected array $classMap = [];

    /** @var Autodiscover[] */
    protected array $autodiscover = [];

    private readonly ListReport $report;

    public function compile( ContainerBuilder $container ) : void
    {
        $this->report = new ListReport( __METHOD__ );

        $this
            ->autodiscoverAnnotatedClasses()
            ->autodiscover();

        $this->report->output();
    }

    protected function getDefinition( string $serviceId, string $className ) : Definition
    {
        if ( $this->container->hasDefinition( $serviceId ) ) {
            return $this->container->getDefinition( $serviceId );
        }
        return new Definition( $className );
    }

    private function autodiscover() : self
    {
        foreach ( $this->autodiscover as $className => $autodiscovered ) {
            $serviceId = $autodiscovered->serviceID;

            $this->report->item( "Registered: {$serviceId}" );

            $definition = $this->getDefinition( $serviceId, $className );
            $interfaces = \class_implements( $className ) ?: [];

            // .. Tags

            if ( $autodiscovered->tag !== null ) {
                foreach ( $autodiscovered->tag as $tagName => $attributes ) {
                    if ( \is_string( $attributes ) ) {
                        $tagName    = $attributes;
                        $attributes = []; // empty properties
                    }
                    elseif ( \is_array( $attributes ) ) {
                        \assert(
                            \is_string( $tagName ),
                            'The Autodiscover->tag properties should be nested. Was provided: '.\print_r(
                                $autodiscovered->tag,
                                true,
                            ),
                        );
                    }
                    $definition->addTag( $tagName, $attributes );
                    $this->report->add( "tagged: '{$tagName}'" );

                    foreach ( $attributes as $attribute => $value ) {
                        $this->report->line( "[{$attribute} => {$value}]" );
                    }
                }
            }

            if ( \in_array( EventSubscriberInterface::class, $interfaces )
                 && $definition->hasTag( 'kernel.event_subscriber' ) === false
            ) {
                $definition->addTag( 'kernel.event_subscriber' );
                $this->report->add( "auto tagged: 'kernel.event_subscriber'" );
            }

            // :: Tags

            if ( $autodiscovered->calls !== null ) {
                $definition->setMethodCalls( $autodiscovered->calls );
            }

            if ( $autodiscovered->bind !== null ) {
                $definition->setBindings( $autodiscovered->bind );
            }

            if ( $autodiscovered->lazy !== null ) {
                $definition->setLazy( $autodiscovered->lazy );
            }

            if ( $autodiscovered->public !== null ) {
                $definition->setPublic( $autodiscovered->public );
            }

            if ( $autodiscovered->shared !== null ) {
                $definition->setShared( $autodiscovered->shared );
            }

            if ( $autodiscovered->autowire !== null ) {
                $definition->setAutowired( $autodiscovered->autowire );
            }

            if ( $autodiscovered->properties !== null ) {
                $definition->setProperties( $autodiscovered->properties );
            }

            if ( $autodiscovered->configurator !== null ) {
                $definition->setConfigurator( $autodiscovered->configurator );
            }

            if ( $autodiscovered->constructor !== null ) {
                // TODO: Autoconfigure::$config->constructor
                throw new BadMethodCallException( 'Autoconfigure::$config->constructor Not implemented' );
            }

            // null = AUTO

            if ( $autodiscovered->alias === null ) {
                $basename = ClassInfo::basename( $className );

                foreach ( $interfaces as $interface ) {
                    if ( \str_starts_with( ClassInfo::basename( $interface ), $basename ) ) {
                        $this->container->setAlias( $interface, $serviceId );
                        $this->report->add( "auto alias: '{$interface}'" );
                    }
                }
            }

            if ( \is_array( $autodiscovered->alias ) ) {
                foreach ( $autodiscovered->alias as $alias ) {
                    $this->container->setAlias( $alias, $serviceId );
                    $this->report->add( "alias: '{$alias}'" );
                }
            }

            $this->container->setDefinition( $serviceId, $definition );

            $this->report->separator();
        }

        return $this;
    }

    private function autodiscoverAnnotatedClasses() : self
    {
        $discover = new ClassFinder();

        $discover
            ->scan( "{$this->projectDirectory}/src" )
            ->scan(
                "{$this->projectDirectory}/vendor",
                'bin',
                'doctrine',
                'composer',
                'latte',
                'monolog',
                'psr',
                'symfony',
                'tempest',
                'twig',
            );

        foreach ( $discover->getFoundClasses() as $className ) {
            if ( ! \class_exists( $className ) ) {
                $this->console->error( [__METHOD__, "Class {$className} does not exist."] );

                continue;
            }

            $reflection = new ReflectionClass( $className );
            $flags      = ReflectionAttribute::IS_INSTANCEOF;
            $attributes = $reflection->getAttributes( Autodiscover::class, $flags );

            if ( empty( $attributes ) ) {
                continue;
            }

            $attributes = \array_pop( $attributes );

            if ( $reflection->getAttributes( Autoconfigure::class, $flags ) ) {
                throw new LogicException(
                    "#[Autodiscover] error for {$className}; cannot use #[Autoconfigure] as well.",
                );
            }

            /** @var Autodiscover $autodiscover */
            $autodiscover = $attributes->newInstance();

            $autodiscover->setClassName( $className );

            $this->autodiscover[$className] = $autodiscover;
        }

        return $this;
    }
}
