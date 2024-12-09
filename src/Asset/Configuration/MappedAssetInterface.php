<?php

declare(strict_types=1);

namespace Core\Symfony\Asset\Configuration;

use Core\Symfony\Asset\{AssetConfigurationInterface, Source};
use Support\FileInfo;

interface MappedAssetInterface extends AssetConfigurationInterface
{
    /**
     * @param string   $name       lowercase, ASCII letters, dot.separated
     * @param FileInfo $source
     * @param Source   $sourceType
     */
    public function __construct(
        string   $name,
        FileInfo $source,
        Source   $sourceType,
    );

    public function getSource() : string;

    /**
     * @return array<array-key, string>
     */
    public function getMappedPaths() : array;
}
