<?php

declare(strict_types=1);

namespace Core\Symfony\Asset;

enum Source : string
{
    case LOCAL  = 'local';
    case REMOTE = 'remote';
    case CDN    = 'cdn';
}
