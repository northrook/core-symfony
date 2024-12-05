<?php

declare(strict_types=1);

namespace Core\Symfony\EventListener;

use InvalidArgumentException;
use Northrook\Clerk;
use Northrook\Logger\Log;
use Core\Symfony\DependencyInjection\{ServiceContainer, ServiceContainerInterface};
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\{ExceptionEvent, KernelEvent};
use function Cache\memoize;
use function Support\{explode_class_callable, get_class_id};

/**
 * @author Martin Nielsen <mn@northrook.com>
 */
abstract class HttpEventListener implements EventSubscriberInterface, ServiceContainerInterface
{
    use ServiceContainer;

    protected string $eventId;

    protected readonly string $listenerId;

    /** @var class-string|false The `Controller` used */
    protected string|false $controller;

    /** @var false|string The `Controller::method` called */
    protected string|false $action;

    public function __construct( protected readonly Clerk $clerk )
    {
        $this->listenerId = get_class_id( $this );
        $this->clerk::event( $this->listenerId, 'http' );
        Log::notice( $this->listenerId.' does this adopt [monolog.tags]?' );
    }

    /**
     * @param KernelEvent                 $event
     * @param class-string<KernelEvent>[] $skip
     *
     * @return bool
     */
    final protected function shouldSkip( KernelEvent $event, array $skip = [ExceptionEvent::class] ) : bool
    {
        $eventId = __METHOD__.'::'.\spl_object_id( $event );

        $this->clerk::event( $eventId, 'http' );

        [$this->controller, $this->action] = memoize(
            callback    : function() use ( $skip, $event ) : array {
                // Check if the `$event` itself should be skipped outright.
                foreach ( $skip as $kernelEvent ) {
                    if ( $event instanceof $kernelEvent ) {
                        Log::info(
                            '{method} skipped event {event}.',
                            ['method' => __METHOD__, 'event' => get_class_id( $event )],
                        );
                        return [false, false];
                    }
                }

                //
                // Get the _controller attribute from the Request object
                $controller = $event->getRequest()->attributes->get( '_controller' );

                // We can safely skip early if the `_controller` is anything but a string
                if ( ! $controller || ! \is_string( $controller ) ) {
                    Log::warning(
                        '{method} Controller attribute was expected be a string. Returning {false}.',
                        ['method' => __METHOD__],
                    );
                    return [false, false];
                }

                // Resolve the `$controller` to a class-string and ensure it exists
                try {
                    [$controller, $method] = explode_class_callable( $controller, true );
                }
                catch ( InvalidArgumentException $classValidationException ) {
                    Log::exception( $classValidationException );
                    return [false, false];
                }

                if ( \is_subclass_of( $controller, ServiceContainerInterface::class ) ) {
                    return [$controller, $method];
                }
                return [false, false];
            },
            key         : \Cache\key(
                [
                    __METHOD__,
                    $event->getRequest()->attributes->get( '_route', $eventId ),
                    ...$skip,
                ],
            ),
            persistence : \Cache\FOREVER,
        );

        $this->clerk::stop( $eventId );

        return ! $this->controller;
    }
}
