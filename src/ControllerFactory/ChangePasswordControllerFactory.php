<?php

namespace Dvsa\Olcs\Auth\ControllerFactory;

use Dvsa\Olcs\Auth\Controller\ChangePasswordController;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

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
        /** @var array $config */
        $config = $container->get('Config');

        $changePasswordService = $container->get('Auth\ChangePasswordService');
        $formHelper = $container->get('Helper\Form');
        $flashMessenger = $container->get('Helper\FlashMessenger');
        $commandSender = $container->get('CommandSender');

        return new ChangePasswordController(
            $changePasswordService,
            $formHelper,
            $flashMessenger,
            $config,
            $commandSender
        );
    }
}
