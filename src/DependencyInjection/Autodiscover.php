<?php

declare(strict_types=1);

namespace Core\Symfony\DependencyInjection;

use Attribute;
use Symfony\Component\DependencyInjection\Argument\TaggedIteratorArgument;
use Symfony\Component\DependencyInjection\Loader\Configurator\ReferenceConfigurator;
use const Support\AUTO;

#[Attribute( Attribute::TARGET_CLASS )]
class Autodiscover
{
    public readonly string $className;

    public readonly string $serviceID;

    /** @var null|array<string, array<string, string>> */
    public readonly ?array $tag;

    /** @var null|class-string[]|false|string[] */
    public readonly null|false|array $alias;

    /**
     * ## `$serviceID`
     * Define a serviceID for this service.
     *
     * Will register using the className by default.
     *
     * ## `$tag`
     * The tags to add to the service.
     *
     * ```
     * Attribute( tag: 'add.single_tag' )
     * Attribute( tag: ['tag.multiple', 'at.once'] )
     * Attribute( tag: ['tag.with_properties' => ['property'=>'argument']] )
     * ```
     *
     * ## `$calls`
     *
     *
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
     * @param null|string                                                             $serviceID
     * @param null|array<array-key, array<string, string>|string>|string              $tag
     * @param null|array<string, ReferenceConfigurator|string|TaggedIteratorArgument> $calls
     * @param null|array<string, string>                                              $bind
     * @param null|bool                                                               $lazy
     * @param null|bool                                                               $public
     * @param null|bool                                                               $shared
     * @param null|bool                                                               $autowire
     * @param null|false|string|string[]                                              $alias
     * @param null|array<string, mixed>                                               $properties
     * @param null|array<class-string, string>|string                                 $configurator
     * @param null|string                                                             $constructor
     */
    public function __construct(
        ?string                           $serviceID = AUTO,
        null|string|array                 $tag = null,
        public readonly ?array            $calls = null,
        public readonly ?array            $bind = null,
        public readonly ?bool             $lazy = null,
        public readonly ?bool             $public = null,
        public readonly ?bool             $shared = null,
        public readonly ?bool             $autowire = null,
        null|false|string|array           $alias = AUTO, // / @ TODO : implement auto-aliasing
        public readonly ?array            $properties = null,
        public readonly null|string|array $configurator = null,
        public readonly ?string           $constructor = null,
    ) {
        if ( $serviceID ) {
            $this->serviceID = $serviceID;
        }

        $this->tag = match ( \is_string( $tag ) ) {
            true    => [$tag => []],
            default => $tag,
        };

        $this->alias = match ( \is_string( $alias ) ) {
            true    => [$alias],
            default => $alias,
        };
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

    /**
     * Override this method to filter the `serviceID` string.
     *
     * @return string
     */
    protected function serviceID() : string
    {
        return $this->className;
    }
}
