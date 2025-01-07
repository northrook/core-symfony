<?php

declare(strict_types=1);

namespace Core\Symfony\Console;

use Stringable;
use Symfony\Component\Console\Helper\FormatterHelper;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Style\SymfonyStyle;

final class Output
{
    public const string
        MARKER = '│',
        DOTTED = '┊';

    private static SymfonyStyle $instance;

    public static function list( ?string $title, string ...$items ) : void
    {
        if ( $title || $items ) {
            Output::symfonyStyle()->newLine();
        }

        if ( $title ) {
            if ( \str_contains( $title, '::' ) ) {
                $title = \trim( \strrchr( $title, '\\' ) ?: $title, '\\' );
            }

            Output::printLine( $title, 'fg=bright-white;options=bold' );
        }

        foreach ( $items as $item ) {
            Output::printLine( $item );
        }

        if ( ! $items ) {
            Output::printLine( 'Empty list', 'fg=red' );
        }

        Output::printLine( '' );
    }

    /**
     * @param string ...$message
     *
     * @return void
     */
    public static function text( string ...$message ) : void
    {
        Output::symfonyStyle()->text( $message );
    }

    /**
     * @param string ...$message
     *
     * @return void
     */
    public static function comment( string ...$message ) : void
    {
        Output::symfonyStyle()->comment( $message );
    }

    /**
     * @param string|string[]      $header
     * @param array<int, string[]> $row
     *
     * @return void
     */
    public static function table( string|array $header, array $row ) : void
    {
        foreach ( $row as $line => $value ) {
            if ( \is_array( $value ) ) {
                continue;
            }
            $row[$line] = [$value];
        }

        Output::symfonyStyle()->table( (array) $header, $row );
    }

    /**
     * @param string ...$message
     *
     * @return void
     */
    public static function success( string ...$message ) : void
    {
        Output::symfonyStyle()->success( $message );
    }

    /**
     * @param string ...$message
     *
     * @return void
     */
    public static function error( string ...$message ) : void
    {
        Output::symfonyStyle()->error( $message );
    }

    /**
     * @param string ...$message
     *
     * @return void
     */
    public static function warning( string ...$message ) : void
    {
        Output::symfonyStyle()->warning( $message );
    }

    /**
     * @param string ...$message
     *
     * @return void
     */
    public static function note( string ...$message ) : void
    {
        Output::symfonyStyle()->note( $message );
    }

    /**
     * @param string ...$message
     *
     * @return void
     */
    public static function info( string ...$message ) : void
    {
        Output::symfonyStyle()->info( $message );
    }

    /**
     * @param string ...$message
     *
     * @return void
     */
    public static function caution( string ...$message ) : void
    {
        Output::symfonyStyle()->caution( $message );
    }

    /**
     * @param string|string[] $message
     * @param string          $style
     * @param bool            $large
     *
     * @return string
     */
    public static function format( string|array $message, string $style, bool $large = false ) : string
    {
        return ( new FormatterHelper() )->formatBlock( $message, $style, $large );
    }

    public static function print( string|Stringable $message, false|string $format = false ) : void
    {
        if ( $format ) {
            $message = Output::format( \trim( (string) $message ), $format );
        }
        self::symfonyStyle()->write( (string) $message );
    }

    public static function printLine( string|Stringable $message, false|string $format = false ) : void
    {
        if ( $format ) {
            $message = Output::format( \trim( (string) $message ), $format );
        }
        self::symfonyStyle()->writeln( (string) $message );
    }

    public static function symfonyStyle() : SymfonyStyle
    {
        return self::$instance ??= new SymfonyStyle( new StringInput( '' ), new ConsoleOutput() );
    }
}
