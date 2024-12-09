<?php

declare(strict_types=1);

namespace Core\Symfony\Asset;

use Stringable;

/**
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
     * @param non-empty-string $name
     * @param non-empty-string $assetID
     * @param non-empty-string $html
     * @param Type             $type
     */
    public function __construct( string $name, string $assetID, string $html, Type $type );

    /**
     * @return string dot.separated lowercase
     */
    public function name() : string;

    /**
     * @return string a 16 character hash
     */
    public function assetID() : string;

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
