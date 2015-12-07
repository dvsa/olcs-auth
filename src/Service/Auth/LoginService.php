<?php

/**
 * Login Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Dvsa\Olcs\Auth\Service\Auth;

use Zend\Http\Response;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Mvc\Controller\Plugin\Redirect;

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
     * @param ServiceLocatorInterface $serviceLocator
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
     * @param $tokenId
     * @param Response $response
     * @param string $goto
     * @return Response
     */
    public function login($tokenId, Response $response, $goto = null)
    {
        $this->cookieService->createTokenCookie($response, $tokenId);

        if ($goto !== null) {
            return $this->redirect->toUrl($goto);
        }

        return $this->redirect->toUrl('/');
    }
}
