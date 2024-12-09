<?php

namespace Core\Symfony\Asset;

use Core\Symfony\Asset\Configuration\Source;

interface ConfigurationInterface
{
    /**
     * Derived from assigned source.
     *
     * @return string
     */
    public function type() : string;

    public function name() : string;

    public function getSourceType() : Source;
}
