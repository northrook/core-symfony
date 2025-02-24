<?php

declare(strict_types=1);

namespace Core\Symfony\DependencyInjection;

trait SettingsAccessor
{
    // protected SettingsProvider $settings;

    private const array DEFAULTS = [
        'auth.onboarding' => true,
    ];

    final protected function settings( string $get ) : string|bool
    {
        return self::DEFAULTS[$get] ?? false;
    }
}
