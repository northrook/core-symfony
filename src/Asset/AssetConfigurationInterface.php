<?php

namespace Core\Symfony\Asset;

use Core\Symfony\SettingsInterface;

interface AssetConfigurationInterface
{
    /**
     * Derived from assigned source.
     *
     * @return Type
     */
    public function type() : Type;

    public function name() : string;

    public function getSourceType() : Source;

    /**
     * @template Setting of array<string, mixed>|null|bool|float|int|string|\UnitEnum
     *
     * @param SettingsInterface<Setting> $settings
     *
     * @return AssetInterface
     */
    public function build( SettingsInterface $settings ) : AssetInterface;
}
