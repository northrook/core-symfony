<?php

declare(strict_types=1);

namespace Core\Symfony\Asset;

/**
 * @used-by AssetInterface
 */
interface InlineInterface
{
    /**
     * Indicate that this `asset` prefers to be inlined.
     *
     * @param null|bool $set
     *
     * @return bool
     */
    public function inline( ?bool $set = null ) : bool;
}
