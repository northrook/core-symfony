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

    public function compile( ContainerBuilder $container ) : void
    {
        $this->autodiscoverAnnotatedClasses();

        $registeredServices = new ListReport( __METHOD__ );

        foreach ( $this->autodiscover as $className => $config ) {
            $serviceId = $config->serviceID;

            $registeredServices->item( $serviceId );

            if ( $container->hasDefinition( $serviceId ) ) {
                $definition = $container->getDefinition( $serviceId );
            }
            else {
                $definition = new Definition( $className );
            }

            $interfaces = \class_implements( $className ) ?: [];

            // .. Tags

            if ( $config->tag !== null ) {
                foreach ( $config->tag as $key => $tag ) {
                    if ( \is_string( $tag ) ) {
                        $key = $tag;
                        $tag = []; // empty properties
                    }
                    elseif ( \is_array( $tag ) ) {
                        \assert(
                            \is_string( $key ),
                            'The Autodiscover->tag properties should be nested. Was provided: '.\print_r(
                                $config->tag,
                                true,
                            ),
                        );
                    }
                    $definition->addTag( $key, $tag );
                    $registeredServices->add( "tagged: '{$key}'" );
                }
            }

            if ( \in_array( EventSubscriberInterface::class, $interfaces ) ) {
                $definition->addTag( 'kernel.event_subscriber' );
                $registeredServices->add( "auto tagged: 'kernel.event_subscriber'" );
            }

            // :: Tags

            if ( $config->calls !== null ) {
                $definition->setMethodCalls( $config->calls );
            }

            if ( $config->bind !== null ) {
                $definition->setBindings( $config->bind );
            }

            if ( $config->lazy !== null ) {
                $definition->setLazy( $config->lazy );
            }

            if ( $config->public !== null ) {
                $definition->setPublic( $config->public );
            }

            if ( $config->shared !== null ) {
                $definition->setShared( $config->shared );
            }

            if ( $config->autowire !== null ) {
                $definition->setAutowired( $config->autowire );
            }

            if ( $config->properties !== null ) {
                $definition->setProperties( $config->properties );
            }

            if ( $config->configurator !== null ) {
                $definition->setConfigurator( $config->configurator );
            }

            if ( $config->constructor !== null ) {
                // TODO: Autoconfigure::$config->constructor
                throw new BadMethodCallException( 'Autoconfigure::$config->constructor Not implemented' );
            }

            // null = AUTO

            if ( $config->alias === null ) {
                $basename = ClassInfo::basename( $className );

                foreach ( $interfaces as $interface ) {
                    if ( \str_starts_with( ClassInfo::basename( $interface ), $basename ) ) {
                        $container->setAlias( $interface, $serviceId );
                        $registeredServices->add( "auto alias: '{$interface}'" );
                    }
                }
            }

            if ( \is_array( $config->alias ) ) {
                foreach ( $config->alias as $alias ) {
                    $container->setAlias( $alias, $serviceId );
                    $registeredServices->add( "alias: '{$alias}'" );
                }
            }

            $container->setDefinition( $serviceId, $definition );
        }

        $registeredServices->output();
    }

    private function autodiscoverAnnotatedClasses() : void
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
    }
}
