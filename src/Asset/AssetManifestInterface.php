<?php

declare(strict_types=1);

namespace Core\Symfony\Asset;

interface AssetManifestInterface
{
    public function registerAsset( AssetBlueprintInterface $blueprint ) : void;

    public function hasAsset( string $name ) : bool;

    /**
     * Returns only manually registered assets.
     *
     * @return AssetBlueprintInterface[]
     */
    public function getRegisteredAssets() : array;

    /**
     * Return an {@see AssetBlueprintInterface} if registered.
     *
     * @param string $asset
     *
     * @return ?AssetBlueprintInterface
     */
    public function getAssetBlueprint( string $asset ) : ?AssetBlueprintInterface;
}
