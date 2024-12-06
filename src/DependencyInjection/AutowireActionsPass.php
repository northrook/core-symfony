<?php

declare(strict_types=1);

namespace Core\Symfony\DependencyInjection;

use Core\Symfony\Console\Output;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Finds all `definitions` implementing the {@see ActionInterface},
 * and ensures they are `autowired` and tagged with `controller.service_arguments`.
 *
 * @author Martin Nielsen <mn@northrook.com>
 */
final class AutowireActionsPass implements CompilerPassInterface
{
    public function process( ContainerBuilder $container ) : void
    {
        $registeredServices = [];

        foreach ( $container->getDefinitions() as $id => $definition ) {
            $service = $definition->getClass();

            if ( ! $service || \str_starts_with( $service, 'Symfony\\' ) ) {
                continue;
            }

            if ( \is_subclass_of( $service, ActionInterface::class, true ) ) {
                $definition->setAutowired( true );
                $definition->addTag( 'controller.service_arguments' );

                $registeredServices[] = [Output::format( '[OK]', 'info' ).$service];
            }
        }

        Output::table( __METHOD__, $registeredServices );
    }
}
