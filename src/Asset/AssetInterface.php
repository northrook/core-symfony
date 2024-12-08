<?php

declare(strict_types=1);

namespace Core\Symfony\Asset;

use Stringable, InvalidArgumentException;

/**
 * @used-by \Core\Symfony\Asset\AssetLocatorInterface
 *
 * @author  Martin Nielsen <mn@northrook.com>
 */
interface AssetInterface
{
    /**
     * Retrieve the `assetId`, a 16 character alphanumeric hash.
     *
     * @return string
     */
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
     * Returns fully resolved `HTML` of the asset.
     *
     * @return string|Stringable
     */
    public function getHtml() : string|Stringable;

    /**
     * Returns the `URL` to the `public` file.
     *
     * Local `assets` will return a relative `path`.
     *
     * @return string
     */
    public function getUrl() : string;

    /**
     * Returns the relative or absolute `path` to the `public` file.
     *
     * @param bool $relative
     *
     * @return string
     *
     * @throws InvalidArgumentException if no local `asset` exists
     */
    public function getPath( bool $relative = true ) : string;

    /**
     * Get the asset version.
     *
     * @return string
     */
    public function version() : string;
}
