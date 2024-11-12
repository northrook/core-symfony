<?php

namespace Core\Symfony\Console;

use Symfony\Component\Console\Formatter\OutputFormatterInterface;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @method static block( string|array $messages, ?string $type = null, ?string $style = null, string $prefix = ' ', bool $padding = false, bool $escape = true ): void
 * @method static title( string $message ): void
 * @method static section( string $message ): void
 * @method static listing( array $elements ): void
 * @method static text( string|array $message ): void
 * @method static comment( string|array $message ): void
 * @method static success( string|array $message ): void
 * @method static error( string|array $message ): void
 * @method static warning( string|array $message ): void
 * @method static note( string|array $message ): void
 * @method static info( string|array $message ): void
 * @method static caution( string|array $message ): void
 */
final class Output extends SymfonyStyle
{
    private static Output $instance;

    public function __construct()
    {
        parent::__construct( new StringInput( null ), new ConsoleOutput() );
    }

    public static function __callStatic( string $name, array $arguments ) : void
    {
        if ( !\method_exists( static::class, $name ) ) {
            throw new \BadMethodCallException( static::class . '::' . $name . '() does not exist' );
        }

        ( Output::$instance ??= new Output() )->$name( ...$arguments );
    }

}
