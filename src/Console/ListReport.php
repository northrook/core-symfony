<?php

declare(strict_types=1);

namespace Core\Symfony\Console;

use Symfony\Component\Stopwatch\{Stopwatch, StopwatchEvent};

final class ListReport
{
    /** @var string[] */
    private array $items = [];

    private readonly StopwatchEvent $stopwatch;

    public readonly string $title;

    public function __construct(
        string                  $title,
        private readonly string $marker = '│',
        private readonly string $note = '┊',
        private readonly string $add = '+',
        private readonly string $remove = '-',
        private readonly string $warning = '◇',
        private readonly string $error = '◈',
        private readonly string $separator = '└',
    ) {
        if ( \str_contains( $title, '::' ) ) {
            $title = \trim( \strrchr( $title, '\\' ) ?: $title, '\\' );
        }
        $this->title     = $title;
        $this->stopwatch = ( new Stopwatch() )->start(
            name     : $this->title,
            category : 'cli_list_report',
        );
    }

    private function addItem( string $message, string $type = 'marker' ) : void
    {
        $this->stopwatch->lap();
        $type = match ( $type ) {
            'note'    => Output::format( $this->note, 'comment' ),
            'warning' => Output::format( $this->warning, 'warning' ),
            'error'   => Output::format( $this->error, 'error' ),
            'add'     => Output::format( $this->add, 'info' ),
            'remove'  => Output::format( $this->remove, 'error' ),
            default   => Output::format( $this->marker, 'fg=bright-green' ),
        };
        $this->items[] = $type.$message;
    }

    public function item( string $message ) : void
    {
        $this->addItem( $message );
    }

    public function note( string $message ) : void
    {
        $this->addItem( $message, 'note' );
    }

    public function warning( string $message ) : void
    {
        $this->addItem( $message, 'note' );
    }

    public function error( string $message ) : void
    {
        $this->addItem( $message, 'note' );
    }

    public function add( string $message ) : void
    {
        $this->addItem( $message, 'add' );
    }

    public function remove( string $message ) : void
    {
        $this->addItem( $message, 'remove' );
    }

    public function separator() : void
    {
        $this->items[] = Output::format( $this->separator, 'comment' );
    }

    public function output() : void
    {
        if ( empty( $this->items ) ) {
            $this->stopwatch->stop();
            return;
        }

        Output::symfonyStyle()->newLine();

        $time = $this->stopwatch->stop()->getDuration();

        $message = Output::format( (string) $time, 'fg=bright-green' );

        $message .= Output::format( $this->title, 'fg=bright-white;options=bold' );

        Output::printLine( $message );

        foreach ( $this->items as $item ) {
            Output::printLine( $item );
        }

        Output::symfonyStyle()->newLine();
    }
}
