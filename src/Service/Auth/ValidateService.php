<?php

namespace Dvsa\Olcs\Auth\Service\Auth;

use Laminas\Http\Headers;
use Laminas\ServiceManager\ServiceLocatorInterface;

/**
 * ValidateService contains method to validate user token on open Am
 */
class ValidateService extends AbstractRestService
{
    /** @var CookieService */
    private $cookieSrv;

    /**
     * Create the logout service
     *
     * @param ServiceLocatorInterface $serviceLocator Service locator
     *
     * @return $this
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $this->cookieSrv = $serviceLocator->get('Auth\CookieService');

        return parent::createService($serviceLocator);
    }

    /**
     * Validate secure token
     *
     * @param string $token Token
     *
     * @return boolean
     */
    public function validate($token)
    {
        $headers = new Headers();
        $headers->addHeaderLine($this->cookieSrv->getCookieName(), $token);

        $response = $this->post('/json/sessions/?_action=validate', [], $headers);

        if ($response->isOk()) {
            return $this->decodeContent($response);
        }

        return null;
    }
}
