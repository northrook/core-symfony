<?php

declare(strict_types=1);

namespace Core\Symfony\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Core\Symfony\Console\Output;
use Support\Interface\ActionInterface;

/**
 * Classes implementing the {@see ActionInterface} are automatically `autowired` and tagged with `controller.service_arguments`.
 *
 * Finds all `definitions` implementing the {@see ActionInterface},
 * and ensures they are `autowired` and tagged with `controller.service_arguments`.
 *
 * @author Martin Nielsen <mn@northrook.com>
 */
final class AutowireActionsPass implements CompilerPassInterface
{
    public function process( ContainerBuilder $container ) : void
    {
        if ( ! \interface_exists( ActionInterface::class ) ) {
            Output::note( "\Support\Interface\ActionInterface does not exist; ".__METHOD__.' skipped.' );
            return;
        }

        $registeredServices = [];

        foreach ( $container->getDefinitions() as $definition ) {
            $service = $definition->getClass();

            if ( ! $service || \str_starts_with( $service, 'Symfony\\' ) ) {
                continue;
            }

            if ( \is_subclass_of( $service, ActionInterface::class ) ) {
                $definition->setAutowired( true );
                $definition->addTag( 'controller.service_arguments' );

                $registeredServices[] = [Output::format( '[OK]', 'info' ).$service];
            }
        }

        if ( ! empty( $registeredServices ) ) {
            Output::table( __METHOD__, $registeredServices );
        }
    }
}
