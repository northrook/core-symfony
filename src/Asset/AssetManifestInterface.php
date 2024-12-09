<?php

declare(strict_types=1);

namespace Core\Symfony\Asset;

interface AssetManifestInterface
{
    /**
     * Returns only manually registered assets.
     *
     * @return array
     */
    public function getRegisteredAssets() : array;

    /**
     * Returns all currently resolved assets.
     *
     * @return array
     */
    public function getResolvedAssets() : array;

    /**
     * Return an {@see AssetInterface} if cached in the `manifest`.
     *
     * @param string $asset
     *
     * @return ?AssetInterface
     */
    public function getAsset( string $asset ) : ?AssetInterface;

    /**
     * Returns an array of configuration options as array. Null on failure.
     *
     * @param string $asset
     *
     * @return null|array{name : string, sources : string[], source : string, prefersInline : null|bool, preload : null|bool, type : string}
     */
    public function getRegisteredConfiguration( string $asset ) : ?array;
}
