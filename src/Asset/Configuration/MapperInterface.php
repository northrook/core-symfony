<?php

declare(strict_types=1);

namespace Core\Symfony\Asset\Configuration;

use Core\Symfony\Asset\ConfigurationInterface;

interface MapperInterface extends ConfigurationInterface
{
    /**
     * @param string $name       lowercase, ASCII letters, dot.separated
     * @param string $sourcePath
     * @param Source $sourceType
     *
     * @return self
     */
    public static function hydrate(
        string $name,
        string $sourcePath,
        Source $sourceType,
    ) : self;

    public function getSource() : string;

    /**
     * @return array<array-key, string>
     */
    public function getMappedPaths() : array;
}
