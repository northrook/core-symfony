<?php

declare(strict_types=1);

namespace Core\Symfony\Asset;

use Core\Symfony\SettingsInterface;
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
     * @param string      $name    lowercase, ASCII letters, dot.separated
     * @param string[]    $source  one or more source files to use
     * @param null|string $assetID [optional] manually set the `assetId`
     * @param null|string $type    [internal] set by the implementing class
     */
    public function __construct(
        string       $name,
        string|array $source,
        ?string      $assetID = null,
        ?string      $type = null,
    );

    /**
     * @template Setting of array<string, mixed>|null|bool|float|int|string|\UnitEnum
     *
     * @param SettingsInterface<Setting> $settings
     *
     * @return string
     */
    public function build( SettingsInterface $settings ) : string;

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
