<?php

declare(strict_types=1);

namespace Core\Symfony;

interface PathfinderInterface
{
    public function get( string $path ) : ?string;

    public function has( string $path ) : bool;
}
