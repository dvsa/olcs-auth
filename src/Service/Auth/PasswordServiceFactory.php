<?php

declare(strict_types = 1);

namespace Dvsa\Olcs\Auth\Service\Auth;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use RuntimeException;

class PasswordServiceFactory implements FactoryInterface
{
    const MSG_MISSING_REALM = 'Auth config is missing the realm';

    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     *
     * @return PasswordService
     * @throws RuntimeException
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): PasswordService
    {
        $config = $container->get('Config');

        if (!isset($config['auth']['realm'])) {
            throw new RuntimeException(self::MSG_MISSING_REALM);
        }

        $commandSender = $container->get('CommandSender');
        $responseDecoder = $container->get('Auth\ResponseDecoderService');

        return new PasswordService($commandSender, $responseDecoder, $config['auth']['realm']);
    }
}
