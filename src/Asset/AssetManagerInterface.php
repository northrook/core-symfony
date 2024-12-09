<?php

namespace Core\Symfony\Asset;

interface AssetManagerInterface
{
    public function __construct(
        array $registeredAssets = [],
    );

    public function resolve( string $asset, ?string $assetClass = null ) : AssetConfigurationInterface;
}
