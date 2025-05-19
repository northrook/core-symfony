<?php

declare(strict_types=1);

namespace Core\Symfony\Compiler;

use Core\Profiler\Interface\Profilable;
use Core\Symfony\DependencyInjection\CompilerPass;
use JetBrains\PhpStorm\Deprecated;
use Core\Symfony\Console\{ListReport};
use Core\Interface\ActionInterface;
use Psr\Log\LoggerAwareInterface;
use Symfony\Component\DependencyInjection\{ContainerBuilder, ContainerInterface, Reference};
use ReflectionClass;
use Throwable;

/**
 * Classes implementing the {@see ActionInterface} are automatically `autowired` and tagged with `controller.service_arguments`.
 *
 * Finds all `definitions` implementing the {@see ActionInterface},
 * and ensures they are `autowired` and tagged with `controller.service_arguments`.
 *
 * @author Martin Nielsen <mn@northrook.com>
 */
final class AutowireInterfaceDependencies extends CompilerPass
{
    private readonly ListReport $report;

    public function compile( ContainerBuilder $container ) : void
    {
        $this->report = new ListReport( __METHOD__ );

        $this
            ->actionInterface()
            ->loggerAwareInterface()
            ->settableProfilerInterface();

        $this->report->output();
    }

    private function actionInterface() : self
    {
        if ( ! \interface_exists( ActionInterface::class ) ) {
            $this->report->error( "\Core\Interface\ActionInterface does not exist; ".__METHOD__.' skipped.' );
            return $this;
        }

        $this->report->item( 'ActionInterface' );

        foreach ( $this->container->getDefinitions() as $service => $definition ) {
            $class = $definition->getClass();
            if ( $this->skip( $class, ActionInterface::class ) ) {
                continue;
            }

            $definition->setAutowired( true );
            $definition->addTag( 'controller.service_arguments' );

            $this->report->add( $service );
        }

        $this->report->separator();

        return $this;
    }

    private function loggerAwareInterface() : self
    {
        if ( ! \interface_exists( LoggerAwareInterface::class ) ) {
            $this->report->error( "\Psr\Log\LoggerAwareInterface does not exist; ".__METHOD__.' skipped.' );
            return $this;
        }

        $this->report->item( 'LoggerAwareInterface' );

        foreach ( $this->container->getDefinitions() as $service => $definition ) {
            $class = $definition->getClass();

            if ( $this->skip( $class, LoggerAwareInterface::class ) ) {
                continue;
            }

            try {
                $reflect         = new ReflectionClass( $class );
                $setLoggerMethod = $reflect->getMethod( 'setLogger' );
                $docBlock        = $setLoggerMethod->getDocComment();
            }
            catch ( Throwable $e ) {
                $this->report->error( $e->getMessage() );

                continue;
            }

            if ( ( $docBlock && \str_contains( $docBlock, '@deprecated' ) )
                 || $reflect->getAttributes( Deprecated::class )
            ) {
                $this->report->warning( 'setLogger deprecated for: '.$class );

                continue;
            }

            $callRegistered = false;

            foreach ( $definition->getMethodCalls() as $methodCall ) {
                if ( $methodCall[0] === 'setLogger' ) {
                    $callRegistered = true;
                }
            }

            if ( $this->verbose && $callRegistered ) {
                $this->report->warning( 'setLogger already set for: '.$service );

                continue;
            }

            $definition->addMethodCall( 'setLogger', [new Reference( 'logger' )] );

            $this->report->add( $service );
        }

        $this->report->separator();
        return $this;
    }

    private function settableProfilerInterface() : self
    {
        if ( ! \interface_exists( Profilable::class ) ) {
            $this->report->error(
                "\Core\Profiler\Interface\SettableProfilerInterface does not exist; ".__METHOD__.' skipped.',
            );
            return $this;
        }

        $this->report->item( 'SettableProfilerInterface' );

        foreach ( $this->container->getDefinitions() as $service => $definition ) {
            $class = $definition->getClass();

            if ( $this->skip( $class, Profilable::class ) ) {
                continue;
            }

            $stopwatch = new Reference(
                'debug.stopwatch',
                ContainerInterface::NULL_ON_INVALID_REFERENCE,
            );

            $definition->addMethodCall( 'setProfiler', [$stopwatch] );
            $this->report->add( $service );
        }

        $this->report->separator();
        return $this;
    }

    /**
     * @param null|string $class
     * @param string      $implements
     *
     * @phpstan-assert-if-false class-string $class
     * @return bool
     */
    protected function skip( ?string $class, string $implements ) : bool
    {
        if ( ! $class ) {
            return true;
        }

        return ! \is_subclass_of( $class, $implements );
    }
}
