<?php

/**
 * Abstract Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Dvsa\Olcs\Auth\Service\Auth;

use Zend\Http\Client;
use Zend\Http\Header\ContentType;
use Zend\Http\Headers;
use Zend\Http\Request;
use Zend\Http\Response;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

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
     * @var string
     */
    private $baseUrl;

    /**
     * @var null|string
     */
    private $realm = null;

    /**
     * Configure a restful service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return $this
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('Config');

        if (empty($config['openam']['url'])) {
            throw new \RuntimeException('openam/url is required but missing from config');
        }

        $this->baseUrl = $config['openam']['url'];
        $this->realm = $config['openam']['realm'];

        $clientOptions = null;
        if (isset($config['openam']['client']['options'])) {
            $clientOptions = $config['openam']['client']['options'];
        }

        $this->client = new Client(null, $clientOptions);

        return $this;
    }

    /**
     * Decode a response content
     *
     * @param Response $response
     * @return array
     */
    protected function decodeContent(Response $response)
    {
        $content = $response->getContent();

        $decoded = json_decode($content, true);

        if ($decoded === false) {
            throw new \RuntimeException('Unable to JSON decode response body: ' . json_last_error_msg());
        }

        $decoded['status'] = $response->getStatusCode();

        return $decoded;
    }

    /**
     * Send a POST
     *
     * @param string $uri
     * @param array $data
     * @param Headers $headers
     * @return Response
     */
    protected function post($uri, $data = [], Headers $headers = null)
    {
        $this->client->reset();
        $this->client->setMethod(Request::METHOD_POST);
        $this->client->setUri($this->buildUri($uri));

        if ($headers === null) {
            $headers = new Headers();
        }

        $headers->addHeader(new ContentType('application/json'));

        $this->client->setHeaders($headers);

        if (!empty($data)) {
            $jsonData = json_encode($data);

            if ($jsonData === false) {
                throw new \RuntimeException('POST data could not be json encoded: ' . json_last_error_msg());
            }

            $this->client->setRawBody($jsonData);
        }

        $response = $this->client->send();

        return $response;
    }

    /**
     * Build a full uri, including the baseUrl, $uri and optionally the realm
     *
     * @param $uri
     * @param bool|true $appendRealm
     * @return string
     */
    protected function buildUri($uri, $appendRealm = true)
    {
        $fullUri = sprintf('%s/%s', rtrim($this->baseUrl, '/'), ltrim($uri, '/'));

        if ($appendRealm && !empty($this->realm)) {

            $joinChar = strstr($fullUri, '?') ? '&' : '?';
            $fullUri .= $joinChar . 'realm=' . $this->realm;
        }

        return $fullUri;
    }
}
