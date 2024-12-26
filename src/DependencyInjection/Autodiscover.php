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

    final public function setClassName( string $className ) : void
    {
        \assert(
            \class_exists( $className ),
            $this::class." expected a valid \$className; {$className} does not exist.",
        );
        $this->className ??= $className;
        $this->setServiceID();
    }

    protected function setServiceID() : void
    {
        if ( ! $this->serviceID ) {
            $this->serviceID = $this->className;
        }
    }
}
