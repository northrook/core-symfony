<?php

declare(strict_types=1);

namespace Core\Symfony\DependencyInjection;

use JetBrains\PhpStorm\Deprecated;

trait SettingsAccessor
{
    // protected SettingsProvider $settings;

    private const array DEFAULTS = [
        'auth.onboarding' => true,
        'toast.timeout'   => 6_400,
    ];

    /**
     * @template Setting of null|array<array-key, scalar>|scalar
     *
     * @param string                                   $key
     * @param null|array|bool|float|int|Setting|string $default
     *
     * @return null|array|bool|float|int|string
     * @phpstan-return Setting
     */
    final protected function getSetting(
        string                           $key,
        null|array|bool|float|int|string $default,
    ) : null|array|bool|float|int|string {
        return self::DEFAULTS[$key] ?? $default;
    }

    /**
     * @internal
     *
     * @param string $get
     *
     * @return bool|string
     */
    #[Deprecated( 'Use getSetting() instead' )]
    final protected function settings( string $get ) : string|bool
    {
        return self::DEFAULTS[$get] ?? false;
    }
}
