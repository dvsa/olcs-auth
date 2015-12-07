<?php

/**
 * Authentication Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Dvsa\Olcs\Auth\Service\Auth;

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
        $data = $this->beginAuthenticationSession();

        $data['callbacks'][0]['input'][0]['value'] = $username;
        $data['callbacks'][1]['input'][0]['value'] = HashService::hashPassword($password);

        $response = $this->post('/json/authenticate', $data);

        return $this->decodeContent($response);
    }

    /**
     * Begin an authentication session in OpenAM
     *
     * @return array|bool
     */
    private function beginAuthenticationSession()
    {
        $response = $this->post('/json/authenticate');

        if ($response->isOk()) {
            return $this->decodeContent($response);
        }

        throw new \RuntimeException('Unable to begin an authentication session');
    }
}
