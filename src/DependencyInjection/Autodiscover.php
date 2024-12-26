<?php

declare(strict_types=1);

namespace Core\Symfony\DependencyInjection;

use Attribute;

#[Attribute( Attribute::TARGET_CLASS )]
class Autodiscover
{
    protected readonly string $className;

    public readonly string $serviceID;

    public function __construct(
        string                   $serviceID = null,
        public ?array            $tags = null,
        public ?array            $calls = null,
        public ?array            $bind = null,
        public ?bool             $lazy = null,
        public ?bool             $public = null,
        public ?bool             $shared = null,
        public ?bool             $autowire = null,
        public ?array            $properties = null,
        public array|string|null $configurator = null,
        public ?string           $constructor = null,
    ) {
        if ( $serviceID ) {
            $this->serviceID = $serviceID;
        }
    }

    final public function setClassName( string $className ) : void
    {
        \assert(
            \class_exists( $className ),
            $this::class." expected a valid \$className; {$className} does not exist.",
        );
        $this->className ??= $className;
        $this->serviceID ??= $this->serviceID();
    }

    protected function serviceID() : string
    {
        return $this->className;
    }
}
