<?php

declare(strict_types=1);

namespace Core\Symfony\Interface;

/**
 * Provides a `method( callback )` argument for {@see Definition::addMethodCall()}
 */
interface FilterInterface
{
    /**
     * @param string $method
     *
     * @return array{string, array{...}}
     */
    public static function callback( string $method ) : array;
}
