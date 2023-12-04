<?php

namespace Dvsa\Olcs\Auth\ControllerFactory;

use Common\Service\Cqrs\Command\CommandSender;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Dvsa\Olcs\Auth\Container\AuthChallengeContainer;
use Dvsa\Olcs\Auth\Controller\ExpiredPasswordController;
use Dvsa\Olcs\Auth\Service\Auth\ExpiredPasswordService;
use Dvsa\Olcs\Auth\Service\Auth\LoginService;
use Interop\Container\ContainerInterface;
use Laminas\Authentication\Storage\Session;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface as NotFoundExceptionInterfaceAlias;

/**
 * Class LogoutControllerFactory
 * @package Dvsa\Olcs\Auth\ControllerFactory
 */
class ExpiredPasswordControllerFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return ExpiredPasswordController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): ExpiredPasswordController
    {
        $container = $container->getServiceLocator();

        return new ExpiredPasswordController(
            new AuthChallengeContainer(),
            $container->get(CommandSender::class),
            $container->get(ExpiredPasswordService::class),
            $container->get(FlashMessengerHelperService::class),
            $container->get(FormHelperService::class),
            $container->get(LoginService::class),
            $container->get(Session::class)
        );
    }

    /**
     * Create LogoutController
     *
     * @param ServiceLocatorInterface $serviceLocator ZF Service locator
     *
     * @return ExpiredPasswordController
     * @deprecated No longer needed in Laminas 3
     */
    public function createService(ServiceLocatorInterface $serviceLocator): ExpiredPasswordController
    {
        return $this->__invoke($serviceLocator, ExpiredPasswordController::class);
    }
}
