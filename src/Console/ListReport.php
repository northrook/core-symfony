<?php

declare(strict_types=1);

namespace Core\Symfony\Console;

use Northrook\{Clerk, ClerkEvent};

final class ListReport
{
    public readonly string $title;

    /** @var string[] */
    private array $items = [];

    private readonly string $marker;

    private readonly string $note;

    private readonly string $add;

    private readonly string $remove;

    private readonly ?ClerkEvent $clerk;

    public function __construct( string $title, string $add = '+', string $remove = '-' )
    {
        if ( \str_contains( $title, '::' ) ) {
            $title = \trim( \strrchr( $title, '\\' ) ?: $title, '\\' );
        }
        $this->clerk  = Clerk::event( $title, 'console' );
        $this->title  = $title;
        $this->marker = Output::format( '│', 'fg=bright-green' );
        $this->note   = Output::format( '┊', 'comment' );
        $this->add    = Output::format( $add, 'info' );
        $this->remove = Output::format( $remove, 'error' );
    }

    public function item( string $message ) : void
    {
        $this->items[] = $this->marker.$message;
    }

    public function note( string $message ) : void
    {
        $this->items[] = $this->note.$message;
    }

    public function add( string $message ) : void
    {
        $this->items[] = $this->add.$message;
    }

    public function remove( string $message ) : void
    {
        $this->items[] = $this->remove.$message;
    }

    public function output() : void
    {
        if ( empty( $this->items ) ) {
            return;
        }
        Output::symfonyStyle()->newLine();

        $message = '';

        if ( $this->clerk ) {
            $time = $this->clerk->getDuration( true );
            $message .= Output::format( (string) $time, 'fg=bright-green' );
        }

        $message .= Output::format( $this->title, 'fg=bright-white;options=bold' );

        Output::printLine( $message );

        foreach ( $this->items as $item ) {
            Output::printLine( $item );
        }

        Output::symfonyStyle()->newLine();
    }
}
