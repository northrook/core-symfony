<?php

declare(strict_types=1);

namespace Core\Symfony\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class AutowireActionsCompilerPass implements CompilerPassInterface
{
    public function process( ContainerBuilder $container ) : void
    {
        foreach ( $container->getDefinitions() as $id => $definition ) {
            dump( [$id, $definition->getClass()] );
        }
    }
}
