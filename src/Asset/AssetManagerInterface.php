<?php

namespace Core\Symfony\Asset;

interface AssetManagerInterface
{
    public function __construct();

    public function getAssetConfiguration( string $name ) : AssetConfigurationInterface;
}
