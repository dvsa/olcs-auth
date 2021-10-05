<?php

namespace Dvsa\Olcs\Auth\Service\Auth\Client;

use Dvsa\Olcs\Auth\Service\Auth\Exception;
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

    /**
     * Configure the uri builder
     *
     * @param ServiceLocatorInterface $serviceLocator Service locator
     *
     * @return $this
     * @throws Exception\RuntimeException
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('Config');

        if (empty($config['openam']['url'])) {
            throw new Exception\RuntimeException('openam/url is required but missing from config');
        }

        $this->baseUrl = $config['openam']['url'];

        if (isset($config['auth']['realm'])) {
            $this->realm = $config['auth']['realm'];
        }

        return $this;
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
