<?php

declare(strict_types=1);

namespace Core\Symfony\Asset;

use Support\Normalize;
use function String\hashKey;
use Stringable;

abstract class AssetBlueprint implements AssetBlueprintInterface
{
    public readonly string $name;

    /** @var string[] */
    public readonly array $sources;

    /**
     * @param string                $name
     * @param string[]|Stringable[] $sources
     * @param Source                $source
     * @param Type                  $type
     */
    public function __construct(
        string                 $name,
        array                  $sources,
        public readonly Source $source,
        public readonly Type   $type,
    ) {
        $this->name    = $this->validateName( $name );
        $this->sources = $this->validateSources( $sources );
    }

    private function validateName( string $name ) : string
    {
        return Normalize::key( $name, '.', 64, true );
    }

    /**
     * @param string[]|Stringable[] $sources
     *
     * @return string[]
     */
    private function validateSources( array $sources ) : array
    {
        foreach ( $sources as $index => $path ) {
            $sources[$index] = (string) $path;
        }
        return $sources;
    }

    final protected function assetID( ?string $assetID ) : string
    {
        $assetID ??= hashKey(
            [$this::class, $this->name, $this->type, $this->source, ...$this->sources],
            'implode',
        );

        \assert(
            \strlen( $assetID ) === 16 && \ctype_alnum( $assetID ),
            'Asset ID must be 16 alphanumeric characters long.',
        );

        return $assetID;
    }
}
