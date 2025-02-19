<?php

declare(strict_types=1);

namespace Core\Symfony\DependencyInjection;

use Core\Symfony\Interface\ServiceContainerInterface;
use Symfony\Contracts\Service\Attribute\Required;
use Symfony\Component\DependencyInjection as Container;

/**
 * @phpstan-require-implements ServiceContainerInterface
 *
 * @author Martin Nielsen <mn@northrook.com>
 */
trait SetServiceLocator
{
    protected readonly Container\ServiceLocator $serviceLocator;

    use ServiceLocator;

    #[Required]
    final public function setServiceLocator( Container\ServiceLocator $serviceLocator ) : void
    {
        $this->serviceLocator = $serviceLocator;
    }
}
