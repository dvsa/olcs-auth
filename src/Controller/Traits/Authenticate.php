<?php

/**
 * Authenticate
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Dvsa\Olcs\Auth\Controller\Traits;

use Dvsa\Olcs\Auth\Service\Auth\AuthenticationService;
use Dvsa\Olcs\Auth\Service\Auth\LoginService;

/**
 * Authenticate
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
trait Authenticate
{
    protected function authenticate($username, $password, $failureCallback)
    {
        $result = $this->getAuthenticationService()->authenticate($username, $password);

        if ($result['status'] != 200) {
            return $failureCallback($result);
        }

        if (isset($result['tokenId'])) {
            return $this->getLoginService()
                ->login($result['tokenId'], $this->getResponse());
        }

        return $this->redirect()->toRoute('auth/expired-password', ['authId' => $result['authId']]);
    }

    /**
     * Get authentication service
     *
     * @return AuthenticationService
     */
    protected function getAuthenticationService()
    {
        return $this->getServiceLocator()->get('Auth\AuthenticationService');
    }

    /**
     * Get login service
     *
     * @return LoginService
     */
    protected function getLoginService()
    {
        return $this->getServiceLocator()->get('Auth\LoginService');
    }
}
