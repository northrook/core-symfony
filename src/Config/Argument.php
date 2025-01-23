<?php

declare(strict_types=1);

namespace Core\Symfony\Config;

use Core\Symfony\Interface\ArgumentInterface;

abstract class Argument implements ArgumentInterface
{
    abstract public static function call( string $method ) : array;
}
