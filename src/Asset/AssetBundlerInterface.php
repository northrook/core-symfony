<?php

namespace Core\Symfony\Asset;

interface AssetBundlerInterface
{
    /**
     * @param AssetInterface|string $asset
     *
     * @return array
     */
    public function getAssetConfiguration( string|AssetInterface $asset ) : array;
}
