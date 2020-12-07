<?php

namespace Dvsa\Olcs\Auth\Service\Auth;

use Dvsa\Olcs\Auth\Service\Auth\Client\Client;
use Laminas\Http\Headers;
use Laminas\Http\Response;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

/**
 * Abstract Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
abstract class AbstractRestService implements FactoryInterface
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @var ResponseDecoderService
     */
    private $responseDecoder;

    /**
     * Configure a restful service
     *
     * @param ServiceLocatorInterface $serviceLocator Service locator
     *
     * @return $this
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $this->client = $serviceLocator->get('Auth\Client');
        $this->responseDecoder = $serviceLocator->get('Auth\ResponseDecoderService');

        return $this;
    }

    /**
     * Decode a response content
     *
     * @param Response $response Response
     *
     * @return array
     */
    protected function decodeContent(Response $response)
    {
        return $this->responseDecoder->decode($response);
    }

    /**
     * Send a POST
     *
     * @param string  $uri     URI
     * @param array   $data    Data
     * @param Headers $headers Headers
     *
     * @return Response
     */
    protected function post($uri, $data = [], Headers $headers = null)
    {
        return $this->client->post($uri, $data, $headers);
    }
}
