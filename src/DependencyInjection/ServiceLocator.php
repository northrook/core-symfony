<?php

declare(strict_types=1);

namespace Core\Symfony\DependencyInjection;

use Core\Symfony\Exception\ServiceContainerException;
use Symfony\Component\DependencyInjection as Container;
use Symfony\Component\HttpFoundation\{Request, RequestStack};
use Northrook\Logger\Log;
use Throwable;

/**
 * @author Martin Nielsen <mn@northrook.com>
 */
trait ServiceLocator
{
    protected readonly Container\ServiceLocator $serviceLocator;

    /**
     * @final
     *
     * @template Service
     *
     * @param class-string<Service> $get
     * @param bool                  $nullable
     *
     * @return ($nullable is true ? null|Service : Service)
     */
    final protected function serviceLocator( string $get, bool $nullable = false ) : mixed
    {
        try {
            $service = match ( $get ) {
                Request::class => $this->serviceLocator->get( RequestStack::class )->getCurrentRequest(),
                default        => $this->serviceLocator->get( $get ),
            };

            \assert( $service instanceof $get );
        }
        catch ( Throwable $exception ) {
            $exception = new ServiceContainerException( $get, previous : $exception );

            $service = $nullable ? null : throw $exception;

            if ( $this->applicationEnvironment( 'dev' ) ) {
                Log::exception( $exception );
            }
        }

        return $service;
    }
}
