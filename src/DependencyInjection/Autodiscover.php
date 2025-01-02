<?php

declare(strict_types=1);

namespace Core\Symfony\DependencyInjection;

use Attribute;
use const Support\AUTO;

#[Attribute( Attribute::TARGET_CLASS )]
class Autodiscover
{
    protected readonly string $className;

    public readonly string $serviceID;

    /**
     * ## `$serviceID`
     * Define a serviceID for this service.
     *
     * Will register using the className by default.
     *
     * ## `$tags`
     *
     * ## `$calls`
     * ## `$bind`
     * ## `$lazy`
     * ## `$public`
     * ## `$shared`
     * ## `$autowire`
     * ## `$alias`
     * ## `$properties`
     * ## `$configurator`
     * ## `$constructor`
     *
     * @param null|string                                                               $serviceID
     * @param null|array<string, array<string, bool|int|string>|bool|int|string>|string $tags
     * @param null|array                                                                $calls
     * @param null|array                                                                $bind
     * @param null|bool                                                                 $lazy
     * @param null|bool                                                                 $public
     * @param null|bool                                                                 $shared
     * @param null|bool                                                                 $autowire
     * @param null|array|false                                                          $alias
     * @param null|array                                                                $properties
     * @param null|array|string                                                         $configurator
     * @param null|string                                                               $constructor
     */
    public function __construct(
        ?string                  $serviceID = AUTO,
        public null|string|array $tags = null,
        public ?array            $calls = null,
        public ?array            $bind = null,
        public ?bool             $lazy = null,
        public ?bool             $public = null,
        public ?bool             $shared = null,
        public ?bool             $autowire = null,
        public null|false|array  $alias = AUTO, // / @ TODO : implement auto-aliasing
        public ?array            $properties = null,
        public null|string|array $configurator = null,
        public ?string           $constructor = null,
    ) {
        if ( $serviceID ) {
            $this->serviceID = $serviceID;
        }
        if ( \is_string( $tags ) ) {
            $this->tags = [$tags];
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
