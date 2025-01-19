<?php

declare(strict_types=1);

namespace Core\Symfony\Compiler;

use Symfony\Component\DependencyInjection\{ContainerBuilder, Definition};
use Symfony\Component\DependencyInjection\Attribute\{Autoconfigure};
use Core\Symfony\DependencyInjection\{Autodiscover, CompilerPass};
use Core\Symfony\Console\{ListReport};
use Support\ClassFinder;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use LogicException, BadMethodCallException;
use function Support\classBasename;
use ReflectionAttribute;
use ReflectionClass;

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

            if ( null !== $config->tag ) {
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

            // null = AUTO

            if ( null === $config->alias ) {
                $basename = classBasename( $className );

                foreach ( $interfaces as $interface ) {
                    if ( \str_starts_with( classBasename( $interface ), $basename ) ) {
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

        $discover->withAttribute( Autodiscover::class )
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
            \assert( \class_exists( $className ) );

            $reflection = new ReflectionClass( $className );
            $flags      = ReflectionAttribute::IS_INSTANCEOF;
            $attributes = $reflection->getAttributes( Autodiscover::class, $flags );

            if ( empty( $attributes ) ) {
                return;
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
