<?php

declare(strict_types=1);

namespace Core\Symfony\DependencyInjection;

use JetBrains\PhpStorm\Deprecated;

trait SettingsAccessor
{
    // protected SettingsProvider $settings;

    private const array DEFAULTS = [
        'auth.onboarding' => true,
    ];

    /**
     * @internal
     *
     * @param string                               $key
     * @param null|array<array-key, scalar>|scalar $default
     *
     * @return null|array<array-key, scalar>|scalar
     */
    final protected function getSetting(
        string                           $key,
        null|array|bool|float|int|string $default,
    ) : null|array|bool|float|int|string {
        // TODO: [m] Use a logger or dump statement here to sniff out all desired settings
        return self::DEFAULTS[$key] ?? $default;
    }

    /**
     * @internal
     *
     * @param string $get
     *
     * @return bool|string
     */
    #[Deprecated( 'Use getSetting() instead')]
    final protected function settings( string $get ) : string|bool
    {
        return self::DEFAULTS[$get] ?? false;
    }
}
