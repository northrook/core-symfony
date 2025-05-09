<?php

namespace Core\Symfony\Interface;

use Symfony\Contracts\Service\Attribute\Required;
use Symfony\Contracts\Service\ServiceCollectionInterface;
use Symfony\Component\DependencyInjection as Container;

interface ServiceContainerInterface
{
    /**
     * @template T of ServiceCollectionInterface
     *
     * @internal
     *
     * @param Container\ServiceLocator<T> $serviceLocator
     *
     * @return void
     */
    #[Required]
    public function setServiceLocator( Container\ServiceLocator $serviceLocator ) : void;
}
