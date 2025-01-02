<?php

declare(strict_types=1);

namespace Core\Symfony\DependencyInjection;

use Core\Symfony\Interface\ServiceContainerInterface;
use Symfony\Contracts\Service\Attribute\Required;
use Symfony\Component\DependencyInjection as Symfony;

/**
 * @phpstan-require-implements ServiceContainerInterface
 *
 * @author Martin Nielsen <mn@northrook.com>
 */
trait SetServiceLocator
{
    protected readonly Symfony\ServiceLocator $serviceLocator;

    use ServiceLocator;

    #[Required]
    final public function setServiceLocator( Symfony\ServiceLocator $serviceLocator ) : void
    {
        $this->serviceLocator = $serviceLocator;
    }
}
