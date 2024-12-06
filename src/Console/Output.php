<?php

declare(strict_types=1);

namespace Core\Symfony\Console;

use Symfony\Component\Console\Helper\FormatterHelper;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Style\SymfonyStyle;

final class Output
{
    private static SymfonyStyle $instance;

    /**
     * @param string ...$message
     *
     * @return void
     */
    public static function text( string ...$message ) : void
    {
        Output::print()->text( $message );
    }

    /**
     * @param string ...$message
     *
     * @return void
     */
    public static function comment( string ...$message ) : void
    {
        Output::print()->comment( $message );
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

        Output::print()->table( (array) $header, $row );
    }

    /**
     * @param string ...$message
     *
     * @return void
     */
    public static function success( string ...$message ) : void
    {
        Output::print()->success( $message );
    }

    /**
     * @param string ...$message
     *
     * @return void
     */
    public static function error( string ...$message ) : void
    {
        Output::print()->error( $message );
    }

    /**
     * @param string ...$message
     *
     * @return void
     */
    public static function warning( string ...$message ) : void
    {
        Output::print()->warning( $message );
    }

    /**
     * @param string ...$message
     *
     * @return void
     */
    public static function note( string ...$message ) : void
    {
        Output::print()->note( $message );
    }

    /**
     * @param string ...$message
     *
     * @return void
     */
    public static function info( string ...$message ) : void
    {
        Output::print()->info( $message );
    }

    /**
     * @param string ...$message
     *
     * @return void
     */
    public static function caution( string ...$message ) : void
    {
        Output::print()->caution( $message );
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

    public static function print() : SymfonyStyle
    {
        return self::$instance ??= new SymfonyStyle( new StringInput( '' ), new ConsoleOutput() );
    }
}
