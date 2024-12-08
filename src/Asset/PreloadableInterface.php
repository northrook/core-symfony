<?php

declare(strict_types=1);

namespace Core\Symfony\Asset;

/**
 * @used-by AssetInterface
 */
interface PreloadableInterface
{
    /**
     * Assets can request to be `preloaded`.
     *
     * This can be used by external services to preload if possible.
     *
     * @return bool
     */
    public function preload() : bool;
}
