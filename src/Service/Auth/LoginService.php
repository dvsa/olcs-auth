<?php

namespace Dvsa\Olcs\Auth\Service\Auth;

use Zend\Http\Request;
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
     * @var Request
     */
    private $request;

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
        $this->request = $serviceLocator->get('Request');
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

        $gotoUrl = $this->request->getQuery('goto', false);
        if ($this->validateGotoUrl($gotoUrl)) {
            return $this->redirect->toUrl($gotoUrl);
        }

        return $this->redirect->toUrl('/');
    }

    /**
     * Validate that the goto URL is valid
     *
     * @param string $gotoUrl Goto URL
     *
     * @return bool
     */
    private function validateGotoUrl($gotoUrl)
    {
        if (!is_string($gotoUrl)) {
            return false;
        }

        // Check that the goto URL is valid, ie it begins with the server name from the request
        $serverUrl = $this->request->getUri()->getScheme() .'://'. $this->request->getUri()->getHost() .'/';
        return strpos($gotoUrl, $serverUrl) === 0;
    }
}
