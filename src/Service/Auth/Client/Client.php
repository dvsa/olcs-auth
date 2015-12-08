<?php

/**
 * Client
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Dvsa\Olcs\Auth\Service\Auth\Client;

use Zend\Http\Client as HttpClient;
use Zend\Http\Header\ContentType;
use Zend\Http\Headers;
use Zend\Http\Request;
use Zend\Http\Response;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Dvsa\Olcs\Auth\Service\Auth\Exception;

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
     * @param ServiceLocatorInterface $serviceLocator
     * @return $this
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('Config');

        $clientOptions = null;
        if (isset($config['openam']['client']['options'])) {
            $clientOptions = $config['openam']['client']['options'];
        }

        $this->setOptions($clientOptions);

        $this->uriBuilder = $serviceLocator->get('Auth\Client\UriBuilder');

        return $this;
    }

    /**
     * Send a POST
     *
     * @param string $uri
     * @param array $data
     * @param Headers $headers
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
