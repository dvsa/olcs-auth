<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Auth\ControllerFactory;

use Dvsa\Olcs\Auth\Controller\ValidateController;
use Dvsa\Olcs\Auth\ControllerFactory\ValidateControllerFactory;
use Dvsa\Olcs\Auth\Service\Auth\CookieService;
use Dvsa\Olcs\Auth\Service\Auth\ValidateService;
use Psr\Container\ContainerInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use LmcRbacMvc\Identity\IdentityProviderInterface;

/**
 * @see ValidateControllerFactory
 */
class ValidateControllerFactoryTest extends MockeryTestCase
{
    public function testServiceCreated(): void
    {
        $identityProvider = m::mock(IdentityProviderInterface::class);

        $serviceContainer = m::mock(ContainerInterface::class);
        $serviceContainer->expects('get')->with(IdentityProviderInterface::class)->andReturn($identityProvider);

        $sut = new ValidateControllerFactory();
        $service = $sut($serviceContainer, ValidateController::class);

        self::assertInstanceOf(ValidateController::class, $service);
    }
}
