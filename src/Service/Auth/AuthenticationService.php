<?php

/**
 * Authentication Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Dvsa\Olcs\Auth\Service\Auth;

use Dvsa\Olcs\Auth\Service\Auth\Callback\NameCallback;
use Dvsa\Olcs\Auth\Service\Auth\Callback\PasswordCallback;
use Dvsa\Olcs\Auth\Service\Auth\Callback\Request;

/**
 * Authentication Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class AuthenticationService extends AbstractRestService
{
    /**
     * Authenticate the user
     *
     * @param $username
     * @param $password
     * @return array
     */
    public function authenticate($username, $password)
    {
        // At first we attempt to authenticate with a hashed password
        $response = $this->sendRequest($username, $password);

        // If we are unsuccessful, we fallback to authentication without hashed password
        // @todo Maybe remove all logic around hashing
        //if ($response->getStatusCode() === 401) {
        //    $response = $this->sendRequest($username, $password, false);
        //}

        return $this->decodeContent($response);
    }

    /**
     * Build the request and send it
     *
     * @param string $username
     * @param string $password
     * @param bool $hash
     * @return \Zend\Http\Response
     */
    private function sendRequest($username, $password, $hash = true)
    {
        $data = $this->beginAuthenticationSession();

        $request = $this->buildRequest($data['authId'], $username, $password, $hash);

        return $this->post('/json/authenticate', $request->toArray());
    }

    /**
     * Build the request object
     *
     * @param string $authId
     * @param string $username
     * @param string $password
     * @param bool $hash
     * @return Request
     */
    private function buildRequest($authId, $username, $password, $hash = true)
    {
        $request = new Request($authId, Request::STAGE_AUTHENTICATE);
        $request->addCallback(new NameCallback('User Name:', 'IDToken1', $username));
        $request->addCallback(new PasswordCallback('Password:', 'IDToken2', $password, $hash));

        return $request;
    }

    /**
     * Begin an authentication session in OpenAM
     *
     * @return array
     */
    private function beginAuthenticationSession()
    {
        $response = $this->post('/json/authenticate');

        if ($response->isOk()) {
            return $this->decodeContent($response);
        }

        throw new Exception\RuntimeException('Unable to begin an authentication session');
    }
}
