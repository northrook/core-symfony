<?php

namespace Core\Symfony\Asset;

interface AssetConfigurationInterface
{
    /**
     * Derived from assigned source.
     *
     * @return Type
     */
    public function type() : Type;

    public function name() : string;

    public function getSourceType() : Source;
}
