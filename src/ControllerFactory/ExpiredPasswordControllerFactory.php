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
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface as NotFoundExceptionInterfaceAlias;

class ExpiredPasswordControllerFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return ExpiredPasswordController
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterfaceAlias
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): ExpiredPasswordController
    {
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
}
