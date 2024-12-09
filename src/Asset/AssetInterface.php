<?php

declare(strict_types=1);

namespace Core\Symfony\Asset;

use Stringable;

/**
 * @property-read string $name
 * @property-read string $assetID
 *
 * @used-by \Core\Symfony\Asset\AssetManagerInterface
 *
 * @author  Martin Nielsen <mn@northrook.com>
 */
interface AssetInterface
{
    /**
     * Used when the {@see AssetManagerInterface} is `calling` the `asset`.
     *
     * This class __only__ handles a fully resolved asset.
     *
     * @param non-empty-string $name    [public]
     * @param non-empty-string $assetID [public]
     * @param non-empty-string $html    [private]
     * @param Type             $type    [private]
     */
    public function __construct(
        string $name,
        string $assetID,
        string $html,
        Type   $type,
    );

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
    public function getHTML() : string|Stringable;
}
