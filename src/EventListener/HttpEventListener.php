<?php

declare(strict_types=1);

namespace Core\Symfony\EventListener;

use Cache, InvalidArgumentException;
use Northrook\Clerk;
use Psr\Log\LoggerInterface;
use Core\Symfony\DependencyInjection\{ServiceContainer, ServiceContainerInterface};
use Symfony\Component\Cache\Adapter\{ArrayAdapter};
use Support\Normalize;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Closure;
use Throwable;
use Symfony\Component\HttpKernel\Event\{ExceptionEvent, KernelEvent};
use Symfony\Contracts\Cache\{CacheInterface, ItemInterface};
use function Support\{classBasename, explode_class_callable};
use function String\hashKey;

/**
 * @author Martin Nielsen <mn@northrook.com>
 */
abstract class HttpEventListener implements EventSubscriberInterface, ServiceContainerInterface
{
    use ServiceContainer;

    private readonly CacheInterface $httpEventCache;

    protected readonly string $listenerId;

    /** @var class-string|false The `Controller` used. */
    protected string|false $controller;

    /** @var false|string The `Controller::method` called. */
    protected string|false $action;

    /** @var string the current `_route` name */
    protected string $route;

    public function __construct(
        protected readonly Clerk            $clerk,
        ?CacheInterface                     $cache = null,
        protected readonly ?LoggerInterface $logger = null,
    ) {
        if ( $cache ) {
            $this->httpEventCache = $cache;
        }
        $this->listenerId = classBasename( $this::class );
        $this->clerk::event( $this->listenerId, 'http' );
    }

    /**
     * @param KernelEvent                 $event
     * @param class-string<KernelEvent>[] $skip
     *
     * @return bool
     */
    final protected function shouldSkip( KernelEvent $event, array $skip = [ExceptionEvent::class] ) : bool
    {
        $eventId = $this->listenerId.'::shouldSkip( '.classBasename( $event ).' )';
        $this->clerk::event( $eventId, 'http' );

        // Only parse GET requests
        if ( ! $event->getRequest()->isMethod( 'GET' ) ) {
            return true;
        }

        $this->route = (string) $event->getRequest()->attributes->get( '_route', '' );

        if ( ! $this->route ) {
            $this->logger?->alert(
                'Expected a {_route} parameter, but none was found.',
                ['requestAttributes' => $event->getRequest()->attributes->all()],
            );
            return true;
        }

        $cacheKey = 'skip.event.'.hashKey( $skip );

        [$this->controller, $this->action] = $this->cache(
            $cacheKey,
            function() use ( $skip, $event, $eventId ) : array {
                // Check if the `$event` itself should be skipped outright.
                foreach ( $skip as $kernelEvent ) {
                    if ( $event instanceof $kernelEvent ) {
                        $this->logger?->info(
                            '{method} skipped event {eventId}.',
                            ['method' => __METHOD__, 'eventId' => $eventId],
                        );
                        return [false, false];
                    }
                }

                //
                // Get the _controller attribute from the Request object
                $controller = $event->getRequest()->attributes->get( '_controller' );

                // We can safely skip early if the `_controller` is anything but a string
                if ( ! $controller || ! \is_string( $controller ) ) {
                    $this->logger?->warning(
                        '{method}: Controller attribute was expected be a string. Returning {false}.',
                        ['method' => __METHOD__],
                    );
                    return [false, false];
                }

                // Resolve the `$controller` to a class-string and ensure it exists
                try {
                    [$controller, $method] = explode_class_callable( $controller, true );
                }
                catch ( InvalidArgumentException $exception ) {
                    $this->logger?->error(
                        $exception->getMessage(),
                        ['exception' => $exception, 'eventId' => $eventId],
                    );
                    return [false, false];
                }

                if ( \is_subclass_of( $controller, ServiceContainerInterface::class ) ) {
                    return [$controller, $method];
                }
                return [false, false];
            },
        );

        $this->clerk::stop( $eventId );

        return ! $this->controller;
    }

    /**
     * @template Type
     *
     * @param string         $key         Key - a hash based on $callback and $arguments will be used if null
     * @param Closure():Type $callback    a function or method to cache, optionally with extra arguments as array values
     * @param ?int           $persistence the duration in seconds for the cache entry
     *
     * @return Type
     * @phpstan-return Type
     */
    final protected function cache( string $key, Closure $callback, ?int $persistence = Cache\AUTO ) : mixed
    {
        $this->httpEventCache ??= new ArrayAdapter(
            0,
            false,
            14_400,
            1_024,
        );

        \assert(
            \ctype_alnum( \str_replace( '.', '', $key ) ),
            'The '.__METHOD__.'( $key .. ) can only contain letters, numbers, and periods.',
        );

        try {
            return $this->httpEventCache->get(
                key      : Normalize::key( [$this->route, $key], '.' ),
                callback : static function( ItemInterface $memo ) use ( $callback, $persistence ) : mixed {
                    $memo->expiresAfter( $persistence );
                    return $callback();
                },
            );
        }
        catch ( Throwable $exception ) {
            $this->logger?->error(
                'Exception thrown when using {runtime}: {message}.',
                [
                    'runtime'   => $this::class,
                    'message'   => $exception->getMessage(),
                    'exception' => $exception,
                ],
            );
        }
        return $callback();
    }
}
