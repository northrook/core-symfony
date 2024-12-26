<?php

declare(strict_types=1);

namespace Core\Symfony\DependencyInjection;

use Attribute;

#[Attribute( Attribute::TARGET_CLASS )]
class Autodiscover
{
    public function __construct( public ?string $serviceId = null ) {}
}
