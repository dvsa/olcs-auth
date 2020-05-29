<?php

namespace Dvsa\Olcs\Auth\Service\Auth;

use Common\Service\User\LastLoginService;
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
     * @var LastLoginService
     */
    private $lastLoginService;

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

        $this->lastLoginService = $serviceLocator->get('Common\Service\User\LastLoginService');

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
    public function login($token, $username, Response $response)
    {
        $gotoUrl = $this->request->getQuery('goto', false);

        $expireInHour = false;

        if (strpos($gotoUrl, 'ms-ofba-authentication-successful') !== false) {
            $expireInHour = true;
        }

        $this->cookieService->createTokenCookie($response, $token, $expireInHour);

        $this->lastLoginService->updateLastLogin($username, $token);

        // The "goto" URL added by openAm is always http, if we are running https, then need to change it
        if ($this->request->getUri()->getScheme() === 'https') {
            $gotoUrl = str_replace('http://', 'https://', $gotoUrl);
        }

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
        if (!is_string($gotoUrl) || empty($gotoUrl)) {
            return false;
        }

        // Check that the goto URL is valid, ie it begins with the server name from the request
        $serverUrl = $this->request->getUri()->getScheme() .'://'. $this->request->getUri()->getHost() .'/';
        return strpos($gotoUrl, $serverUrl) === 0;
    }
}
