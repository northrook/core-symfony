<?php

declare(strict_types=1);

namespace Core\Symfony\Exception;

use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Throwable;

class ServiceContainerException extends InvalidArgumentException implements NotFoundExceptionInterface
{
    public function __construct(
        public readonly string $id,
        ?string                $message = null,
        ?Throwable             $previous = null,
    ) {
        $message ??= $this->getMessage();

        parent::__construct( $message, 500, $previous );
    }
}
