<?php

declare(strict_types=1);

namespace Core\Symfony\Asset\Configuration;

enum Source : string
{
    case LOCAL  = 'local';
    case REMOTE = 'remote';
    case CDN    = 'cdn';
}
