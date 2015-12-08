<?php

/**
 * Uri Builder
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Dvsa\Olcs\Auth\Service\Auth\Client;

use Zend\Http\Response;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Dvsa\Olcs\Auth\Service\Auth\Exception;

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
     * @param ServiceLocatorInterface $serviceLocator
     * @return $this
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('Config');

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
     * Build a full uri, including the baseUrl, $uri and optionally the realm
     *
     * @param $uri
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
