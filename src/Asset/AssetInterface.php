<?php

declare(strict_types=1);

namespace Core\Symfony\Asset;

use Stringable, InvalidArgumentException;

/**
 * @property-read string $name
 * @property-read string $type
 *
 * @used-by \Core\Symfony\Asset\AssetLocatorInterface
 *
 * @author  Martin Nielsen <mn@northrook.com>
 */
interface AssetInterface
{
    /**
     * Used when the {@see AssetLocatorInterface} is `calling` the `asset`.
     *
     * This class __only__ handles a fully resolved asset.
     *
     * @param non-empty-lowercase-string                                   $name
     * @param string[]                                                     $source     one or more source files to use
     * @param Type                                                         $type
     * @param array<string, null|array<array-key, string>|bool|int|string> $attributes
     * @param null|string                                                  $assetID    [optional] manually set the `assetId`
     */
    public function __construct(
        string       $name,
        string|array $source,
        Type         $type,
        array        $attributes = [],
        ?string      $assetID = null,
    );

    /**
     * Retrieve the `assetId`, a 16 character alphanumeric hash.
     *
     * @return string
     */
    public function assetId() : string;

    /**
     * Returns the asset `type` by default.
     *
     * @param null|string|Type $is
     *
     * @return ( $is is string ? bool : Type )
     */
    public function type( null|string|Type $is = null ) : Type|bool;

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
     * Returns an array of each `source`.
     *
     * @return array
     */
    public function getSources() : array;

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
