<?php

declare(strict_types=1);

namespace Core\Symfony\EventListener;

use InvalidArgumentException;
use Northrook\Clerk;
use Northrook\Logger\Log;
use Core\Symfony\DependencyInjection\{ServiceContainer, ServiceContainerInterface};
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\{ExceptionEvent, KernelEvent};
use function Support\{get_class_name};

/**
 *
 */
abstract class HttpEventListener implements EventSubscriberInterface, ServiceContainerInterface
{
    use ServiceContainer;

    private string $eventId;

    private string $listenerId;

    private array $cache = [];

    private array $events = [];

    // TODO : Provide an in-memory/file cache for handleController and other simple calls
    final public function __construct()
    {
        $this->listenerId = $this::class.'::'.\spl_object_hash( $this );
        Clerk::event( __METHOD__, $this::class );
        Log::notice( __METHOD__.' does this adopt [monolog.tags]?' );
    }

    /**
     * @param KernelEvent                 $event
     * @param class-string<KernelEvent>[] $skip
     *
     * @return bool
     */
    final protected function shouldSkip( KernelEvent $event, array $skip = [ExceptionEvent::class] ) : bool
    {
        $this->eventId = $event::class.'::'.\spl_object_id( $event );

        $this->events[][$this->eventId] = $event::class;
        Clerk::event( __METHOD__, $this->eventId );

        // Check if the `$event` itself should be skipped outright.
        foreach ( $skip as $kernelEvent ) {
            if ( $event instanceof $kernelEvent ) {
                return true;
            }
        }

        dump( $event );

        return $this->cache[$this->eventId] ??= ( function() use ( $event ) : bool {
            //
            // Get the _controller attribute from the Request object
            $controller = $event->getRequest()->attributes->get( '_controller' );

            // We can safely skip early if the `_controller` is anything but a string
            if ( ! $controller || ! \is_string( $controller ) ) {
                Log::warning(
                    '{method} Controller attribute was expected be a string. Returning {false}.',
                    ['method' => __METHOD__],
                );
                return false;
            }

            // Resolve the `$controller` to a class-string and ensure it exists
            try {
                $controller = get_class_name( $controller, true );
            }
            catch ( InvalidArgumentException $classValidationException ) {
                Log::exception( $classValidationException );
                return false;
            }

            return \is_subclass_of( $controller, ServiceContainerInterface::class );
        } )();
    }
}
