<?php

namespace Dvsa\Olcs\Auth\ControllerFactory;

use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Dvsa\Olcs\Auth\Controller\ResetPasswordController;
use Dvsa\Olcs\Auth\Service\Auth\PasswordService;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

/**
 * Class ResetPasswordControllerFactory
 * @package Dvsa\Olcs\Auth\ControllerFactory
 */
class ResetPasswordControllerFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return ResetPasswordController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): ResetPasswordController
    {
        /** @var ServiceLocatorInterface $sm */
        $sm = $container->getServiceLocator();

        /** @var FormHelperService $formHelperService */
        $formHelperService = $sm->get('Helper\Form');

        /** @var FlashMessengerHelperService $flashMessenger */
        $flashMessenger = $sm->get('Helper\FlashMessenger');

        /** @var PasswordService $passwordService */
        $passwordService = $sm->get(PasswordService::class);

        return new ResetPasswordController(
            $formHelperService,
            $flashMessenger,
            $passwordService,
        );
    }

    /**
     * Create ResetPasswordController
     *
     * @param ServiceLocatorInterface $serviceLocator ZF Service locator
     *
     * @return ResetPasswordController
     * @deprecated No longer needed in Laminas 3
     */
    public function createService(ServiceLocatorInterface $serviceLocator): ResetPasswordController
    {
        return $this($serviceLocator, ResetPasswordController::class);
    }
}
