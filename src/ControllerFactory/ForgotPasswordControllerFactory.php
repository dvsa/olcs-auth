<?php

namespace Dvsa\Olcs\Auth\ControllerFactory;

use Common\Service\Helper\FormHelperService;
use Dvsa\Olcs\Auth\Controller\ForgotPasswordController;
use Dvsa\Olcs\Auth\Service\Auth\PasswordService;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Class ForgotPasswordControllerFactory
 * @package Dvsa\Olcs\Auth\ControllerFactory
 */
class ForgotPasswordControllerFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return ForgotPasswordController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): ForgotPasswordController
    {
        /** @var ServiceLocatorInterface $sm */
        $sm = $container->getServiceLocator();

        /** @var FormHelperService $formHelperService */
        $formHelperService = $sm->get('Helper\Form');

        /** @var PasswordService $passwordService */
        $passwordService = $sm->get(PasswordService::class);

        return new ForgotPasswordController(
            $formHelperService,
            $passwordService,
        );
    }

    /**
     * Create ForgotPasswordController
     *
     * @param ServiceLocatorInterface $serviceLocator ZF Service locator
     *
     * @return ForgotPasswordController
     * @deprecated No longer needed in Laminas 3
     */
    public function createService(ServiceLocatorInterface $serviceLocator): ForgotPasswordController
    {
        return $this($serviceLocator, ForgotPasswordController::class);
    }
}
