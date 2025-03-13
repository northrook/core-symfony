<?php

declare(strict_types=1);

namespace Core\Symfony\DependencyInjection;

use Core\Interface\SettingsInterface;
use Psr\Log\{LoggerAwareInterface, LoggerInterface};
use UnitEnum;

final class SettingsProvider implements SettingsInterface, LoggerAwareInterface
{
    private const array DEFAULTS = [
        'auth.onboarding' => true,
        'toast.timeout'   => 6_400,
    ];

    public function __construct(
        protected ?LoggerInterface $logger = null,
    ) {}

    public function get( string $key, mixed $default = null ) : bool|string|int|float|UnitEnum|null
    {
        \assert(
            ! empty( $key ) && \ctype_alpha( \str_replace( ['.', '-'], '', $key ) ),
            "Invalid settings key '{$key}'. Must be non-empty and contain only letters, numbers, dots and dashes.",
        );

        return self::DEFAULTS[$key] ?? $default;
    }

    public function has( string $key ) : bool
    {
        return isset( self::DEFAULTS[$key] );
    }

    /**
     * Sets a logger.
     *
     * @internal
     *
     * @param LoggerInterface $logger
     */
    final public function setLogger( LoggerInterface $logger ) : void
    {
        $this->logger ??= $logger;
    }
}
