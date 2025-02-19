<?php

declare(strict_types=1);

namespace Core\Symfony\Cache;

use Cache\LocalStoragePool;
use Symfony\Component\Cache\Adapter\ProxyAdapter;

final class LocalStorageAdapter extends ProxyAdapter
{
    public function __construct(
        string $filePath,
        string $namespace = '',
        int    $defaultLifetime = 0,
        bool   $autosave = true,
        bool   $validate = true,
    ) {
        if ( ! $namespace ) {
            $namespace = \basename( $filePath );
            $namespace = \strrchr( $namespace, '.', true ) ?: $namespace;
        }
        parent::__construct(
            new LocalStoragePool(
                $filePath,
                $namespace,
                $this::class,
                $autosave,
                $validate,
                $defaultLifetime,
            ),
            $namespace,
            $defaultLifetime,
        );
    }
}
