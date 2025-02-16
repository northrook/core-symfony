<?php

declare(strict_types=1);

namespace Core\Symfony\Config;

use Core\Symfony\Interface\ArgumentInterface;
use JetBrains\PhpStorm\Deprecated;

#[Deprecated]
abstract class Argument implements ArgumentInterface
{
    /**
     * @param string $method
     *
     * @return array<string, callable>
     */
    abstract public static function call( string $method ) : array;
}
