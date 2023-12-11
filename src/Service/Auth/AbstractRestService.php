<?php

namespace Dvsa\Olcs\Auth\Service\Auth;

use Dvsa\Olcs\Auth\Service\Auth\Client\Client;
use Interop\Container\ContainerInterface;
use Laminas\Http\Headers;
use Laminas\Http\Response;
use Laminas\ServiceManager\Factory\FactoryInterface;

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

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $this->client = $container->get('Auth\Client');
        $this->responseDecoder = $container->get('Auth\ResponseDecoderService');

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
