<?php

namespace Dvsa\Olcs\Auth\ControllerFactory;

use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Dvsa\Olcs\Auth\Controller\ResetPasswordController;
use Dvsa\Olcs\Auth\Service\Auth\PasswordService;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

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
        /** @var FormHelperService $formHelperService */
        $formHelperService = $container->get('Helper\Form');

        /** @var FlashMessengerHelperService $flashMessenger */
        $flashMessenger = $container->get('Helper\FlashMessenger');

        /** @var PasswordService $passwordService */
        $passwordService = $container->get(PasswordService::class);

        return new ResetPasswordController(
            $formHelperService,
            $flashMessenger,
            $passwordService,
        );
    }
}
