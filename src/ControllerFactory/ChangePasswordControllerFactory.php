<?php

namespace Dvsa\Olcs\Auth\ControllerFactory;

use Dvsa\Olcs\Auth\Controller\ChangePasswordController;
use Dvsa\Olcs\Auth\Controller\LogoutController;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

/**
 * Class LogoutControllerFactory
 * @package Dvsa\Olcs\Auth\ControllerFactory
 */
class ChangePasswordControllerFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return ChangePasswordController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): ChangePasswordController
    {
        /** @var ServiceLocatorInterface $sm */
        $sm = $container->getServiceLocator();

        /** @var array $config */
        $config = $sm->get('Config');

        $changePasswordService = $sm->get('Auth\ChangePasswordService');
        $formHelper = $sm->get('Helper\Form');
        $flashMessenger = $sm->get('Helper\FlashMessenger');
        $commandSender = $sm->get('CommandSender');

        return new ChangePasswordController(
            $changePasswordService,
            $formHelper,
            $flashMessenger,
            $config,
            $commandSender
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
    public function createService(ServiceLocatorInterface $serviceLocator): ChangePasswordController
    {
        return $this($serviceLocator, ChangePasswordController::class);
    }
}
