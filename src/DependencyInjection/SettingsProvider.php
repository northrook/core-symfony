<?php

declare(strict_types=1);

namespace Core\Symfony\DependencyInjection;

use Core\Interface\SettingsInterface;
use Psr\Log\{LoggerAwareInterface, LoggerAwareTrait};
use UnitEnum;

final class SettingsProvider implements SettingsInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    private const array DEFAULTS = [
        'auth.onboarding' => true,
        'toast.timeout'   => 6_400,
    ];

    public function __construct() {}

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
}
