<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Auth\ControllerFactory;

use Dvsa\Olcs\Auth\Controller\ValidateController;
use Dvsa\Olcs\Auth\Service\Auth\CookieService;
use Dvsa\Olcs\Auth\Service\Auth\ValidateService;
use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use LmcRbacMvc\Identity\IdentityProviderInterface;

/**
 * @see ValidateController
 */
class ValidateControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): ValidateController
    {
        $identityProvider = $container->get(IdentityProviderInterface::class);
        return new ValidateController($identityProvider);
    }
}
