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
        private readonly string $warning = '⋄',
        private readonly string $error = '!',
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
            'note'    => Output::format( $this->note, 'fg=gray;options=bold' ),
            'skip'    => Output::format( $this->note, 'fg=green' ),
            'warning' => Output::format( $this->warning, 'fg=yellow;options=bold' ),
            'error'   => Output::format( $this->error, 'error' ),
            'add'     => Output::format( $this->add, 'info' ),
            'remove'  => Output::format( $this->remove, 'error' ),
            default   => Output::format( $this->marker, 'fg=bright-green' ),
        };
        $this->items[] = $type.$message;
    }

    public function line( string $message, int $indnet = 3) :void
    {
        if( $indnet ){
            $message = \str_repeat( ' ', $indnet ).$message;
        }

        $this->items[] = $message;
    }

    public function item( string $message ) : void
    {
        $this->addItem( $message );
    }

    public function skip( string $message ) : void
    {
        $this->addItem( $message, 'skip' );
    }

    public function note( string $message ) : void
    {
        $this->addItem( $message, 'note' );
    }

    public function warning( string $message ) : void
    {
        $this->addItem( $message, 'warning' );
    }

    public function error( string $message ) : void
    {
        $this->addItem( $message, 'error' );
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
        $this->items[] = '';
    }

    public function output() : void
    {
        if ( empty( $this->items ) ) {
            $this->stopwatch->stop();
            return;
        }

        Output::symfonyStyle()->newLine();

        $time = $this->stopwatch->stop()->getDuration();

        $style_time = 'fg=gray;options=bold';
        $style_fade = 'fg=gray;';

        $message = " <{$style_time}>{$time}</{$style_time}><{$style_fade}>ms</{$style_fade}>";
        $message .= Output::format( $this->title, 'fg=bright-white;options=bold' );

        Output::printLine( $message );

        foreach ( $this->items as $item ) {
            Output::printLine( $item );
        }

        Output::symfonyStyle()->newLine();
    }
}
