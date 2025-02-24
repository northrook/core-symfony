<?php

declare(strict_types=1);

namespace Core\Symfony\DependencyInjection;

use Psr\Log\{LoggerAwareInterface, LoggerAwareTrait};

final class SettingsProvider implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    private const array DEFAULTS = [
        'auth.onboarding' => true,
    ];

    public function __construct() {}

    public function get( string $key, mixed $default = null ) : mixed
    {
        return self::DEFAULTS[$key] ?? $default;
    }
}
