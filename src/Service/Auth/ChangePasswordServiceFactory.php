<?php

declare(strict_types = 1);

namespace Dvsa\Olcs\Auth\Service\Auth;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use RuntimeException;

class ChangePasswordServiceFactory implements FactoryInterface
{
    const MSG_MISSING_REALM = 'Auth config is missing the realm';

    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     *
     * @return ChangePasswordService
     * @throws RuntimeException
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): ChangePasswordService
    {
        $config = $container->get('Config');

        if (!isset($config['auth']['realm'])) {
            throw new RuntimeException(self::MSG_MISSING_REALM);
        }

        $commandSender = $container->get('CommandSender');
        $responseDecoder = $container->get('Auth\ResponseDecoderService');

        return new ChangePasswordService($commandSender, $responseDecoder, $config['auth']['realm']);
    }

    /**
     * @deprecated Can be removed following Laminas V3 upgrade
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return ChangePasswordService
     * @throws RuntimeException
     */
    public function createService(ServiceLocatorInterface $serviceLocator): ChangePasswordService
    {
        return $this($serviceLocator, ChangePasswordService::class);
    }
}
