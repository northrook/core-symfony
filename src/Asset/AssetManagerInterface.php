<?php

declare(strict_types=1);

namespace Core\Symfony\Asset;

use Psr\Log\LoggerInterface;
use Symfony\Contracts\Cache\CacheInterface;

/**
 * @author Martin Nielsen <mn@northrook.com>
 */
interface AssetManagerInterface
{
    /**
     * @param AssetManifestInterface $manifest
     * @param string                 $assetBuildDirectory
     * @param string                 $publicAssetDirectory
     * @param null|LoggerInterface   $logger
     * @param null|CacheInterface    $cache
     */
    public function __construct(
        AssetManifestInterface $manifest,
        string                 $assetBuildDirectory,
        string                 $publicAssetDirectory,
        ?LoggerInterface       $logger = null,
        ?CacheInterface        $cache = null,
    );

    /**
     * Add one or more assets to be located when {@see self::getEnqueuedAssets} is called.
     *
     * @param string ...$name
     *
     * @return void
     */
    public function enqueueAsset( string ...$name ) : void;

    public function hasAsset( string $name ) : bool;

    /**
     * Locate and return an {@see AssetInterface}.
     *
     * Implementing classes *must* ensure `null` returns on missing `assets` are logged using the provided {@see LoggerInterface}.
     *
     * @param string $name
     *
     * @param array<string, array<array-key|string>|string> $attributes
     *
     * @return ?AssetInterface
     */
    public function renderAsset( string $name, array $attributes = [] ) : ?AssetInterface;

    /**
     * Returns an array all `enqueued` assets as `HTML` strings.
     *
     * The resolved assets may be cached using the  provided {@see CacheInterface}.
     *
     * @param bool $cached
     *
     * @return array<string, AssetInterface>
     */
    public function resolveEnquuedAssets( bool $cached = true ) : array;

    /**
     * Returns a list of all currently `enqueued` assets.
     *
     * @return string[]
     */
    public function getEnqueuedAssets() : array;
}
