<?php

/**
 * Logout Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Dvsa\Olcs\Auth\Service\Auth;

use Zend\Http\Headers;
use Zend\ServiceManager\ServiceLocatorInterface;

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
     * @param ServiceLocatorInterface $serviceLocator
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
     * @param string $tokenId
     * @return boolean
     */
    public function logout($tokenId)
    {
        $headers = new Headers();
        $headers->addHeaderLine($this->cookieService->getCookieName(), $tokenId);

        $response = $this->post('/json/sessions/?_action=logout', [], $headers);

        return $response->isOk();
    }
}
