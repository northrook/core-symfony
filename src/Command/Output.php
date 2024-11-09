<?php

namespace Core\Symfony\Command;

// use Symfony\Component\Console\Output as Symfony;

use Symfony\Component\Console\Formatter\OutputFormatterInterface;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class Output extends BufferedOutput
{

    public function __construct(
            ?int                      $verbosity = OutputInterface::VERBOSITY_NORMAL,
            bool                      $decorated = false,
            ?OutputFormatterInterface $formatter = null,
    )
    {
        $formatter ??= new SymfonyStyle( );
        parent::__construct( $verbosity, $decorated, $formatter );
    }
}
