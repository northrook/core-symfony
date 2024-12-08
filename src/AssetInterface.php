<?php

declare(strict_types=1);

namespace Core\Symfony;

use Stringable, InvalidArgumentException;

/**
 * @used-by \Core\Symfony\AssetLocatorInterface
 *
 * @author  Martin Nielsen <mn@northrook.com>
 */
interface AssetInterface
{
    public const string NAME = '';

    public function assetId() : string;

    /**
     * Returns the asset `type` by default.
     *
     * @param null|string $is Check if the asset is of `type`
     *
     * @return ( $is is string ? bool : string )
     */
    public function type( ?string $is = null ) : string|bool;

    /**
     * Get the asset version.
     *
     * @return string
     */
    public function version() : string;

    /**
     * Assets can request to be `preloaded`.
     *
     * This can be used by external services to preload if possible.
     *
     * @return bool
     */
    public function preload() : bool;

    /**
     * Indicate that this `asset` prefers to be inlined.
     *
     * @param null|bool $set
     *
     * @return bool
     */
    public function inline( ?bool $set = null ) : bool;

    /**
     * Returns fully resolved `HTML` of the asset.
     *
     * @return string|Stringable
     */
    public function getHtml() : string|Stringable;

    /**
     * Returns the `URL` to this `asset`.
     *
     * Local `assets` will return a relative `path`.
     *
     * @return string
     */
    public function getUrl() : string;

    /**
     * Returns the relative or absolute `path` to this `asset`.
     *
     * @param bool $relative
     *
     * @return string
     *
     * @throws InvalidArgumentException if no local `asset` exists
     */
    public function getPath( bool $relative = true ) : string;
}
