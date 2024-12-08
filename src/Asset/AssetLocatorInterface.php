<?php

declare(strict_types=1);

namespace Core\Symfony\Asset;

use Psr\Log\LoggerInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Stringable;

/**
 * @author Martin Nielsen <mn@northrook.com>
 */
interface AssetLocatorInterface
{
    public function __construct(
        ?LoggerInterface $logger = null,
        ?CacheInterface  $cache = null,
    );

    /**
     * Add one or more assets to be located when {@see self::getEnqueuedAssets} is called.
     *
     * @param AssetInterface|string ...$asset
     *
     * @return void
     */
    public function enqueueAsset( string|AssetInterface ...$asset ) : void;

    /**
     * Locate and return an {@see AssetInterface}.
     *
     * Implementing classes *must* ensure `null` returns on missing `assets` are logged using the provided {@see LoggerInterface }.
     *
     * @param AssetInterface|string $asset Key as `string` or `AssetInterface`
     *
     * @return ?AssetInterface
     */
    public function getAsset( string|AssetInterface $asset ) : ?AssetInterface;

    /**
     * @param AssetInterface|string ...$asset
     *
     * @return AssetInterface[]
     */
    public function getAssets( string|AssetInterface ...$asset ) : array;

    /**
     * Returns an array all `enqueued` assets as `HTML` strings.
     *
     * The resolved assets may be cached using the  provided {@see CacheInterface}.
     *
     * @param bool $cached
     *
     * @return array<string, string|Stringable>
     */
    public function getResolvedAssets( bool $cached = true ) : array;

    /**
     * Returns a list of all currently `enqueued` assets.
     *
     * @return string[]
     */
    public function getEnqueuedAssets() : array;
}
