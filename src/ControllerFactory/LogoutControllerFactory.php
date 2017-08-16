<?php

namespace Dvsa\Olcs\Auth\ControllerFactory;

use Zend\ServiceManager\ServiceLocatorInterface;
use Dvsa\Olcs\Auth\Controller\LogoutController;
use Dvsa\Olcs\Auth\Service\Auth\LogoutService;
use Dvsa\Olcs\Auth\Service\Auth\CookieService;
use Zend\ServiceManager\FactoryInterface;
use Zend\Http\PhpEnvironment\Request;
use Zend\Http\Response;

/**
 * Class LogoutControllerFactory
 * @package Dvsa\Olcs\Auth\ControllerFactory
 */
class LogoutControllerFactory implements FactoryInterface
{
    const OPENAM_HEADER_REALM_KEY = 'HTTP_X_REALM';

    /**
     * Create LogoutController
     *
     * @param ServiceLocatorInterface $serviceLocator ZF Service locator
     *
     * @return LogoutController
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var ServiceLocatorInterface $sm */
        $sm = $serviceLocator->getServiceLocator();

        /** @var array $config */
        $config = $sm->get('config');

        /** @var Request $requestService */
        $requestService = $sm->get('request');

        /** @var Response $responseService */
        $responseService = new Response();

        /** @var CookieService $cookieService */
        $cookieService = $sm->get('Auth\CookieService');

        /** @var LogoutService $logoutService */
        $logoutService = $sm->get('Auth\LogoutService');

        return new LogoutController(
            $requestService,
            $responseService,
            $cookieService,
            $logoutService,
            $this->isSelfServeUser($requestService),
            $this->getSelfServeLogoutUrl($config)
        );
    }

    /**
     * Check if the current session is self serve
     *
     * @param Request $requestService Zend request service
     *
     * @return bool
     */
    private function isSelfServeUser(Request $requestService)
    {
        $realmName = $requestService->getServer(self::OPENAM_HEADER_REALM_KEY);
        return ($realmName === 'selfserve' || empty($realmName));
    }

    /**
     * Retrieve URL to use when we redirect Self Serve user
     *
     * @param array $config Config from service locator
     *
     * @return string
     */
    private function getSelfServeLogoutUrl(array $config)
    {
        if (empty($config['selfserve_logout_redirect_url'])) {
            throw new \InvalidArgumentException('Selfserve logout redirect is not available in config');
        }

        return $config['selfserve_logout_redirect_url'];
    }
}
