<?php

namespace Core\Symfony\Profiler;

use Override;
use Symfony\Bundle\FrameworkBundle\DataCollector\AbstractDataCollector;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Throwable;
use Symfony\Component\HttpFoundation\{Request, Response};
use function Support\toString;

final class ParameterBagCollector extends AbstractDataCollector
{
    public function __construct(
        private readonly ParameterBagInterface $parameterBag,
    ) {
    }

    #[Override]
    public function collect( Request $request, Response $response, ?Throwable $exception = null ) : void
    {
        foreach ( $this->parameterBag->all() as $key => $value ) {
            $this->data[] = [
                'label' => $key,
                'value' => $this->paramter( $value ),
            ];
        }
    }

    public function getParameterCount() : int
    {
        return \count( $this->data );
    }

    /**
     * @return array<int, array{label: string, value: string}>
     */
    public function getParamterItems() : array
    {
        return $this->data;
    }

    private function paramter( mixed $value ) : string
    {
        if ( \is_null( $value ) ) {
            return 'null';
        }

        if ( \is_string( $value ) || \is_int( $value ) || \is_float( $value ) ) {
            return (string) $value;
        }

        $paramter = [];

        if ( \is_iterable( $value ) ) {
            foreach ( $value as $valueItem ) {
                $paramter[] = $this->paramter( $valueItem );
            }
        }
        else {
            return toString( $value );
        }

        return \implode( ', ', $paramter );
    }
}
