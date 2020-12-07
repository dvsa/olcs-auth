<?php

namespace Dvsa\Olcs\Auth\Service\Auth\Client;

use Dvsa\Olcs\Auth\Service\Auth\Exception;
use Laminas\Http\Client as HttpClient;
use Laminas\Http\Header\ContentType;
use Laminas\Http\Headers;
use Laminas\Http\Request;
use Laminas\Http\Response;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

/**
 * Client
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class Client extends HttpClient implements FactoryInterface
{
    /**
     * @var UriBuilder
     */
    private $uriBuilder;

    /**
     * Configure the client
     *
     * @param ServiceLocatorInterface $serviceLocator Service locator
     *
     * @return $this
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('Config');

        $clientOptions = null;
        if (isset($config['openam']['client']['options'])) {
            $clientOptions = $config['openam']['client']['options'];
        }

        $this->uriBuilder = $serviceLocator->get('Auth\Client\UriBuilder');
        $this->setOptions($clientOptions);

        return $this;
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
    public function post($uri, $data = [], Headers $headers = null)
    {
        $this->reset();
        $this->setMethod(Request::METHOD_POST);
        $this->setUri($this->uriBuilder->build($uri));

        if ($headers === null) {
            $headers = new Headers();
        }

        $headers->addHeader(new ContentType('application/json'));

        $this->setHeaders($headers);

        if (!empty($data)) {
            $jsonData = json_encode($data);

            if ($jsonData === false) {
                throw new Exception\RuntimeException('POST data could not be json encoded: ' . json_last_error_msg());
            }

            $this->setRawBody($jsonData);
        }

        $response = $this->send();

        return $response;
    }
}
