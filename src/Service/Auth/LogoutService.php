<?php

namespace Dvsa\Olcs\Auth\Service\Auth;

use Laminas\Http\Headers;
use Laminas\ServiceManager\ServiceLocatorInterface;

/**
 * Logout Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class LogoutService extends AbstractRestService
{
    /**
     * @var CookieService
     */
    private $cookieService;

    /**
     * Create the logout service
     *
     * @param ServiceLocatorInterface $serviceLocator Service locator
     *
     * @return $this
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $this->cookieService = $serviceLocator->get('Auth\CookieService');

        return parent::createService($serviceLocator);
    }

    /**
     * Delete the session from OpenAM
     *
     * @param string $token Token
     *
     * @return boolean
     */
    public function logout($token)
    {
        $headers = new Headers();
        $headers->addHeaderLine($this->cookieService->getCookieName(), $token);

        $response = $this->post('/json/sessions/?_action=logout', [], $headers);

        return $response->isOk();
    }
}
