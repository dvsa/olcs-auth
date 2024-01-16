<?php

namespace Dvsa\Olcs\Auth\ControllerFactory;

use Common\Service\Helper\FormHelperService;
use Dvsa\Olcs\Auth\Controller\ForgotPasswordController;
use Dvsa\Olcs\Auth\Service\Auth\PasswordService;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class ForgotPasswordControllerFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return ForgotPasswordController
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): ForgotPasswordController
    {
        /** @var FormHelperService $formHelperService */
        $formHelperService = $container->get('Helper\Form');

        /** @var PasswordService $passwordService */
        $passwordService = $container->get(PasswordService::class);

        return new ForgotPasswordController(
            $formHelperService,
            $passwordService,
        );
    }
}
