<?php

/** @noinspection PhpClassCanBeReadonlyInspection */

namespace Core\Symfony\EventListener;

use Core\Symfony\DependencyInjection\ServiceContainerInterface;
use Northrook\Clerk;
use Northrook\Logger\Log;
use Symfony\Component\HttpFoundation\Request;
use function Support\get_class_name;

abstract class ResponseEventListener implements ServiceContainerInterface
{
    private readonly ?string $controller;

    // TODO : Provide an in-memory/file cache for handleController and other simple calls
    public function __construct()
    {
        Clerk::event( __METHOD__, $this::class );
    }

    /**
     * Check if the passed {@see Request} is using a controller implementing the {@see ServiceContainerInterface}.
     *
     * @param Request $request
     *
     * @return bool
     */
    final protected function handleController( Request $request ) : bool
    {
        Clerk::event( __METHOD__, $this::class );

        if ( $request->attributes->has( 'exception' ) ) {
            return false;
        }

        if ( ! isset( $this->controller ) ) {
            $_controller = $request->attributes->get( '_controller' );

            if ( ! \is_string( $_controller ) ) {
                Log::warning(
                    '{method} Controller attribute was expected be a string. Returning {false}.',
                    ['method' => __METHOD__],
                );
                return false;
            }
            $this->controller = get_class_name( $_controller );
        }

        if ( ! $this->controller || ! \class_exists( $this->controller ) ) {
            return false;
        }

        return \is_subclass_of( $this->controller, ServiceContainerInterface::class );
    }
}
