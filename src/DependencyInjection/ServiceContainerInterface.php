<?php

declare(strict_types=1);

namespace Core\Symfony\DependencyInjection;

use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Contracts\Service\Attribute\Required;
use Symfony\Contracts\Service\ServiceCollectionInterface;

interface ServiceContainerInterface
{
    /**
     * @template T of ServiceCollectionInterface
     *
     * @param ServiceLocator<T> $serviceLocator
     *
     * @return void
     */
    #[Required]
    public function setServiceLocator( ServiceLocator $serviceLocator ) : void;
}
