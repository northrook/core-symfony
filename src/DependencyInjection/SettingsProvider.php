<?php

declare(strict_types=1);

namespace Core\Symfony\DependencyInjection;

use Core\Interface\SettingsProviderInterface;
use Psr\Log\{LoggerAwareInterface, LoggerInterface};
use UnitEnum;

final class SettingsProvider implements SettingsProviderInterface, LoggerAwareInterface
{
    private const array DEFAULTS = [
        'auth.onboarding' => true,
        'toast.timeout'   => 6_400,
    ];

    public function __construct(
        protected ?LoggerInterface $logger = null,
    ) {}

    /**
     * Sets a logger.
     *
     * @internal
     *
     * @param LoggerInterface $logger
     */
    final public function setLogger( LoggerInterface $logger ) : void
    {
        $this->logger ??= $logger;
    }

    public function get(
        string                           $key,
        float|array|bool|int|string|null $default,
    ) : null|array|bool|float|int|string {
        \assert(
            ! empty( $key ) && \ctype_alpha( \str_replace( ['.', '-'], '', $key ) ),
            "Invalid settings key '{$key}'. Must be non-empty and contain only letters, numbers, dots and dashes.",
        );

        return self::DEFAULTS[$key] ?? $default;
    }

    public function versions( string $settings, ?int $limit = null ) : array
    {
        $this->logger?->alert(
            'TODO: {method} not yet implemented in {class}.',
            [':method' => __METHOD__, ':class' => __CLASS__],
        );
        return [];
    }

    public function restore( string $setting, int $versionId ) : bool
    {
        $this->logger?->alert(
                'TODO: {method} not yet implemented in {class}.',
                [':method' => __METHOD__, ':class' => __CLASS__],
        );
        return false;
    }

    public function add( array $parameters ) : void
    {
        $this->logger?->alert(
                'TODO: {method} not yet implemented in {class}.',
                [':method' => __METHOD__, ':class' => __CLASS__],
        );
    }

    public function has( string $setting ) : bool
    {
        $this->logger?->alert(
                'TODO: {method} not yet implemented in {class}.',
                [':method' => __METHOD__, ':class' => __CLASS__],
        );
        return false;
    }

    public function all() : array
    {
        $this->logger?->alert(
                'TODO: {method} not yet implemented in {class}.',
                [':method' => __METHOD__, ':class' => __CLASS__],
        );
        return [];
    }

    public function reset() : void
    {
        $this->logger?->alert(
                'TODO: {method} not yet implemented in {class}.',
                [':method' => __METHOD__, ':class' => __CLASS__],
        );
    }

    public function remove( string $name ) : void
    {
        $this->logger?->alert(
                'TODO: {method} not yet implemented in {class}.',
                [':method' => __METHOD__, ':class' => __CLASS__],
        );
    }

    public function set( string $name, UnitEnum|float|array|bool|int|string|null $value ) : void
    {
        $this->logger?->alert(
                'TODO: {method} not yet implemented in {class}.',
                [':method' => __METHOD__, ':class' => __CLASS__],
        );
    }
}
