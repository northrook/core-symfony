<?php

declare(strict_types=1);

namespace Core\Symfony\Asset;

use Psr\Log\LoggerInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Stringable;

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
     * @return ?AssetInterface
     */
    public function getAsset( string $name ) : ?AssetInterface;

    /**
     * @param string ...$name
     *
     * @return AssetInterface[]
     */
    public function getAssets( string ...$name ) : array;

    /**
     * Returns an array all `enqueued` assets as `HTML` strings.
     *
     * The resolved assets may be cached using the  provided {@see CacheInterface}.
     *
     * @param bool $cached
     *
     * @return array<string, string|Stringable>
     */
    public function resolveEnquuedAssets( bool $cached = true ) : array;

    /**
     * Returns a list of all currently `enqueued` assets.
     *
     * @return string[]
     */
    public function getEnqueuedAssets() : array;
}
