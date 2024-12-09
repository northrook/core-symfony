<?php

namespace Core\Symfony\Asset;

use Core\Symfony\SettingsInterface;
use RuntimeException;

interface AssetBlueprintInterface
{

    /**
     * @template Setting of array<string, mixed>|null|bool|float|int|string|\UnitEnum
     *
     * @param string                     $assetBuildDirectory
     * @param string                     $publicAssetDirectory
     * @param SettingsInterface<Setting> $settings
     * @param ?string                    $assetId
     *
     * @return self
     *
     * @throws RuntimeException
     */
    public function build(
        string            $assetBuildDirectory,
        string            $publicAssetDirectory,
        SettingsInterface $settings,
        ?string           $assetId = null,
    ) : self;

    /**
     * @param null|array<string, array<array-key|string>|string> $attributes
     *
     * @return AssetInterface
     */
    public function render( ?array $attributes = null ) : AssetInterface;

    /**
     * Get the asset version.
     *
     * @return string
     */
    public function version() : string;
}
