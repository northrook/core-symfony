<?php

declare(strict_types=1);

namespace Core\Symfony\DependencyInjection;

use Support\Normalize;

abstract class ServiceConfigurator
{
    /**
     * @param array<string, bool|float|int|string|string[]> $parameters
     */
    final protected function __construct( protected array $parameters )
    {
        foreach ( $this->parameters as $key => $value ) {
            \assert( \is_string( $key ), 'Parameter keys must be strings.' );
            \assert( ! \ctype_digit( $key[0] ), 'Parameter keys cannot start with numbers.' );
        }
    }

    /**
     * @return array<string, bool|float|int|string|string[]>
     */
    final protected function toArray() : array
    {
        return $this->parameters;
    }

    final protected function normalizePathParameters() : void
    {
        foreach ( $this->parameters as $paramterKey => $value ) {
            if ( $this->keyHintsPathOrDirectory( $paramterKey ) ) {
                if ( \is_array( $value ) ) {
                    foreach ( $value as $arrayKey => $arrayValue ) {
                        $value[$arrayKey] = Normalize::path( $arrayValue );
                    }
                }

                if ( \is_string( $value ) ) {
                    $value = Normalize::path( $value );
                }

                $this->parameters[$paramterKey] = $value;
            }
        }
    }

    final protected function keyHintsPathOrDirectory( string $key ) : bool
    {
        return
                \str_ends_with( $key, 'Directory' )
                || \str_ends_with( $key, 'Directories' )
                || \str_ends_with( $key, 'Path' )
                || \str_ends_with( $key, 'Paths' );
    }
}
