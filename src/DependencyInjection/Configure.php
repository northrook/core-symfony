<?php

declare(strict_types=1);

namespace Core\Symfony\DependencyInjection;

use Support\Normalize;

function keyHintsPathOrDirectory( string $key ) : bool
{
    return (bool) (
        \str_ends_with( $key, 'Directory' )
            || \str_ends_with( $key, 'Directories' )
            || \str_ends_with( $key, 'Path' )
            || \str_ends_with( $key, 'Paths' )
    );
}

final class Configure
{
    private function __construct() {}

    public static function parameters( array $get_defined_vars ) : array
    {
        foreach ( $get_defined_vars as $paramterKey => $value ) {
            if ( keyHintsPathOrDirectory( $paramterKey ) ) {
                if ( \is_array( $value ) ) {
                    foreach ( $value as $arrayKey => $arrayValue ) {
                        $value[$arrayKey] = Normalize::path( $arrayValue );
                    }
                }

                if ( \is_string( $value ) ) {
                    $value = Normalize::path( $value );
                }
            }
        }

        return $get_defined_vars;
    }
}
