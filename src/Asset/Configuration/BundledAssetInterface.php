<?php

declare(strict_types=1);

namespace Core\Symfony\Asset\Configuration;

use Core\Symfony\Asset\{AssetConfigurationInterface, Source};

interface BundledAssetInterface extends AssetConfigurationInterface
{
    /**
     * @param string              $name          lowercase, ASCII letters, dot.separated
     * @param \Support\FileInfo[] $sources       one or more source files to use
     * @param Source              $sourceType
     * @param bool                $prefersInline
     * @param ?bool               $preload
     */
    public function __construct(
        string $name,
        array  $sources,
        Source $sourceType,
        ?bool  $prefersInline = null,
        ?bool  $preload = null,
    );

    /**
     * @return string[]
     */
    public function getSources() : array;

    public function getBundlePath() : string;

    /**
     * Indicate that this `asset` prefers to be inlined.
     *
     * @return null|bool
     */
    public function prefersInline() : ?bool;

    /**
     * Assets can request to be `preloaded`.
     *
     * This can be used by external services to preload if possible.
     *
     * @return bool
     */
    public function preload() : bool;
}
