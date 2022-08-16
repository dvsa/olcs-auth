<?php

namespace Dvsa\Olcs\Auth\Service\Auth\Client;

use Dvsa\Olcs\Auth\Service\Auth\Exception;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

/**
 * Uri Builder
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class UriBuilder implements FactoryInterface
{
    /**
     * @var string
     */
    private $baseUrl;

    /**
     * @var null|string
     */
    private $realm = null;

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): UriBuilder
    {
        $config = $container->get('Config');

        if (empty($config['openam']['url'])) {
            throw new Exception\RuntimeException('openam/url is required but missing from config');
        }

        $this->baseUrl = $config['openam']['url'];

        if (isset($config['openam']['realm'])) {
            $this->realm = $config['openam']['realm'];
        }

        return $this;
    }

    /**
     * Configure the uri builder
     *
     * @param ServiceLocatorInterface $serviceLocator Service locator
     *
     * @return $this
     * @throws Exception\RuntimeException
     * @deprecated No longer needed in Laminas 3
     */
    public function createService(ServiceLocatorInterface $serviceLocator): UriBuilder
    {
        return $this($serviceLocator, UriBuilder::class);
    }

    /**
     * Build a full uri, including the baseUrl, $uri and optionally the realm
     *
     * @param string $uri URI
     *
     * @return string
     */
    public function build($uri)
    {
        $fullUri = sprintf('%s/%s', rtrim($this->baseUrl, '/'), ltrim($uri, '/'));

        if (!empty($this->realm)) {
            $joinChar = strstr($fullUri, '?') ? '&' : '?';
            $fullUri .= $joinChar . 'realm=' . $this->realm;
        }

        return $fullUri;
    }
}
