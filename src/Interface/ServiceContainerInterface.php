<?php

declare(strict_types=1);

namespace Core\Symfony\Interface;

use Symfony\Contracts\Service\Attribute\Required;
use Symfony\Contracts\Service\ServiceCollectionInterface;
use Symfony\Component\DependencyInjection as Container;

interface ServiceContainerInterface
{
    /**
     * @template T of ServiceCollectionInterface
     *
     * @param Container\ServiceLocator<T> $serviceLocator
     *
     * @return void
     */
    #[Required]
    public function setServiceLocator( Container\ServiceLocator $serviceLocator ) : void;
}
