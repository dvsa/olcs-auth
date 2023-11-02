<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Auth\ControllerFactory;

use Dvsa\Olcs\Auth\Controller\ValidateController;
use Dvsa\Olcs\Auth\Service\Auth\CookieService;
use Dvsa\Olcs\Auth\Service\Auth\ValidateService;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use LmcRbacMvc\Identity\IdentityProviderInterface;

/**
 * @see ValidateController
 */
class ValidateControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): ValidateController
    {
        $cookieService = $container->get('Auth\CookieService');
        $validateService = $container->get(ValidateService::class);
        $identityProvider = $container->get(IdentityProviderInterface::class);

        assert($cookieService instanceof CookieService);
        assert($validateService instanceof ValidateService);
        assert($identityProvider instanceof IdentityProviderInterface);

        return new ValidateController($cookieService, $validateService, $identityProvider);
    }
}
