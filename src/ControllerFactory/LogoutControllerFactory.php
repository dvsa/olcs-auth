<?php

namespace Dvsa\Olcs\Auth\ControllerFactory;

use Common\Rbac\PidIdentityProvider;
use Dvsa\Olcs\Auth\Controller\LogoutController;
use Dvsa\Olcs\Auth\Service\Auth\CookieService;
use Dvsa\Olcs\Auth\Service\Auth\LogoutService;
use Interop\Container\ContainerInterface;
use Laminas\Http\PhpEnvironment\Request;
use Laminas\Http\Response;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\Session\Container;
use RuntimeException;

/**
 * Class LogoutControllerFactory
 * @package Dvsa\Olcs\Auth\ControllerFactory
 */
class LogoutControllerFactory implements FactoryInterface
{
    protected const HEADER_REALM_KEY = 'HTTP_X_REALM';

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): LogoutController
    {
        /** @var ServiceLocatorInterface $sm */
        $sm = $container->getServiceLocator();

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

        $sessionName = $config['auth']['session_name'] ?? '';
        if (empty($sessionName)) {
            throw new RunTimeException("Missing auth.session_name from config");
        }
        $session = new Container($sessionName);

        return new LogoutController(
            $requestService,
            $responseService,
            $cookieService,
            $logoutService,
            $this->isSelfServeUser($requestService),
            $this->getSelfServeLogoutUrl($config),
            $session
        );
    }

    /**
     * Create LogoutController
     *
     * @param ServiceLocatorInterface $serviceLocator ZF Service locator
     *
     * @return LogoutController
     * @deprecated No longer needed in Laminas 3
     */
    public function createService(ServiceLocatorInterface $serviceLocator): LogoutController
    {
        return $this($serviceLocator, LogoutController::class);
    }

    /**
     * Check if the current session is self serve
     *
     * @param Request $requestService Laminas request service
     *
     * @return bool
     */
    private function isSelfServeUser(Request $requestService)
    {
        $realmName = $requestService->getServer(self::HEADER_REALM_KEY);
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
