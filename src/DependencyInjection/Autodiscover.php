<?php

declare(strict_types=1);

namespace Core\Symfony\DependencyInjection;

use Attribute;

#[Attribute( Attribute::TARGET_CLASS )]
class Autodiscover
{
    protected readonly string $className;

    public function __construct(
        public string            $serviceID = '',
        public ?array            $tags = null,
        public ?array            $calls = null,
        public ?array            $bind = null,
        public bool|string|null  $lazy = null,
        public ?bool             $public = null,
        public ?bool             $shared = null,
        public ?bool             $autowire = null,
        public ?array            $properties = null,
        public array|string|null $configurator = null,
        public ?string           $constructor = null,
    ) {}

    public function setClassName( string $className ) : void
    {
        \assert( \is_string( $className ) && \class_exists( $className ) );
        $this->className ??= $className;
    }

    protected function setServiceID() : void
    {
        $this->serviceID ??= $this->className;
    }
}
