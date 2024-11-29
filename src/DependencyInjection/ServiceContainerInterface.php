<?php

declare(strict_types=1);

namespace Core\Symfony\DependencyInjection;

use Symfony\Contracts\Service\Attribute\Required;
use Symfony\Contracts\Service\ServiceCollectionInterface;
use Symfony\Component\DependencyInjection as Symfony;

interface ServiceContainerInterface
{
    /**
     * @template T of ServiceCollectionInterface
     *
     * @param Symfony\ServiceLocator<T> $serviceLocator
     *
     * @return void
     */
    #[Required]
    public function setServiceLocator( Symfony\ServiceLocator $serviceLocator ) : void;
}
