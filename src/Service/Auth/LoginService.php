<?php

namespace Dvsa\Olcs\Auth\Service\Auth;

use Common\Service\User\LastLoginService;
use Interop\Container\ContainerInterface;
use Laminas\Http\Request;
use Laminas\Http\Response;
use Laminas\Mvc\Controller\Plugin\Redirect;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

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
     * @var Request
     */
    private $request;

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): LoginService
    {
        $this->cookieService = $container->get('Auth\CookieService');
        $this->lastLoginService = $container->get('Common\Service\User\LastLoginService');
        $this->request = $container->get('Request');

        return $this;
    }

    /**
     * Create the login service
     *
     * @param ServiceLocatorInterface $serviceLocator Service locator
     *
     * @return $this
     * @deprecated No longer needed in Laminas 3
     */
    public function createService(ServiceLocatorInterface $serviceLocator): LoginService
    {
        return $this($serviceLocator, LoginService::class);
    }

    /**
     * Login and redirect
     *
     * @param string $token Token
     * @param Response $response Response
     *
     * @return string
     */
    public function login($token, Response $response): string
    {
        $gotoUrl = $this->request->getQuery('goto', false);

        $expireInHour = false;

        if (strpos($gotoUrl, 'ms-ofba-authentication-successful') !== false) {
            $expireInHour = true;
        }

        $this->cookieService->createTokenCookie($response, $token, $expireInHour);

        $this->lastLoginService->updateLastLogin($token);

        // The "goto" URL added by openAm is always http, if we are running https, then need to change it
        if ($this->request->getUri()->getScheme() === 'https') {
            $gotoUrl = str_replace('http://', 'https://', $gotoUrl);
        }

        if ($this->validateGotoUrl($gotoUrl)) {
            return $gotoUrl;
        }

        return '/';
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
        $serverUrl = $this->request->getUri()->getScheme() . '://' . $this->request->getUri()->getHost() . '/';
        return strpos($gotoUrl, $serverUrl) === 0;
    }
}
