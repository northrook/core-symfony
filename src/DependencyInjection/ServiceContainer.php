<?php

declare(strict_types=1);

namespace Core\Symfony\DependencyInjection;

use Core\Symfony\Interface\ServiceContainerInterface;
use JetBrains\PhpStorm\Deprecated;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * @phpstan-require-implements ServiceContainerInterface
 *
 * @author Martin Nielsen <mn@northrook.com>
 */
#[Deprecated]
trait ServiceContainer
{
    use SetServiceLocator;

    /**
     * @final
     *
     * @param null|'debug'|'dev'|'prod'|'test' $is
     *
     * @return bool|string
     */
    final protected function applicationEnvironment( ?string $is = null ) : string|bool
    {
        $env   = $this->getParameterBag()->get( 'kernel.environment' );
        $debug = $this->getParameterBag()->get( 'kernel.debug' );

        \assert( \is_string( $env ) && \is_bool( $debug ) );

        // Log a warning if debugging is enabled in production.
        if ( $debug && $env === 'prod'
                    && \property_exists( $this, 'logger' )
                    && $this->logger instanceof LoggerInterface
        ) {
            $this->logger->critical( '{Debug} enabled in production.' );
        }

        // Stand-alone debug check
        if ( $is === 'debug' && $debug ) {
            return true;
        }

        // True if the environment matches asked, or true if we are debugging anywhere but production
        if ( $env === $is || ( $is && $env !== 'prod' && $debug ) ) {
            return true;
        }

        // Return the environment string
        return $env;
    }

    final protected function getParameterBag() : ParameterBagInterface
    {
        return $this->serviceLocator( ParameterBagInterface::class );
    }
}
