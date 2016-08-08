<?php

namespace Dvsa\Olcs\Auth\Service\Auth;

use Zend\Http\Response;
use Zend\Mvc\Controller\Plugin\Redirect;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Login Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class LoginService implements FactoryInterface
{
    /**
     * @var CookieService
     */
    private $cookieService;

    /**
     * @var Redirect
     */
    private $redirect;

    /**
     * Create the login service
     *
     * @param ServiceLocatorInterface $serviceLocator Service locator
     *
     * @return $this
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $this->cookieService = $serviceLocator->get('Auth\CookieService');

        $cpm = $serviceLocator->get('ControllerPluginManager');

        $this->redirect = $cpm->get('redirect');

        return $this;
    }

    /**
     * Login and redirect
     *
     * @param string   $token    Token
     * @param Response $response Response
     *
     * @return Response
     */
    public function login($token, Response $response)
    {
        $this->cookieService->createTokenCookie($response, $token);

        return $this->redirect->toUrl('/');
    }
}
