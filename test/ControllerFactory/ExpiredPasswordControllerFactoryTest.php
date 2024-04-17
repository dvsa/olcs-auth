<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Auth\ControllerFactory;

use Common\Rbac\JWTIdentityProvider;
use Common\Service\Cqrs\Command\CommandSender;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Test\MocksServicesTrait;
use Dvsa\Olcs\Auth\Controller\ExpiredPasswordController;
use Dvsa\Olcs\Auth\ControllerFactory\ExpiredPasswordControllerFactory;
use Dvsa\Olcs\Auth\Service\Auth\ExpiredPasswordService;
use Dvsa\Olcs\Auth\Service\Auth\LoginService;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Psr\Container\ContainerInterface;
use Laminas\Authentication\Storage\Session;
use Laminas\ServiceManager\ServiceManager;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Auth\Adapter\SelfserveCommandAdapter;
use Olcs\Service\Helper\WebDavJsonWebTokenGenerationService;
use Olcs\Service\Helper\WebDavJsonWebTokenGenerationServiceFactory;

class ExpiredPasswordControllerFactoryTest extends MockeryTestCase
{
    use MocksServicesTrait;

    protected ExpiredPasswordControllerFactory $sut;

    private const CONFIG_VALID = [
        'auth' => [
            'identity_provider' => JWTIdentityProvider::class
        ]
    ];

    /**
     * @test
     */
    public function invokeIsCallable(): void
    {
        // Setup
        $this->setUpSut();

        // Assert
        $this->assertIsCallable(fn(\Psr\Container\ContainerInterface $container, string $requestedName, ?array $options = null): \Dvsa\Olcs\Auth\Controller\ExpiredPasswordController => $this->sut->__invoke($container, $requestedName, $options));
    }

    /**
     * @test
     * @depends invokeIsCallable
     */
    public function invokeReturnsAnInstanceOfExpiredPasswordController(): void
    {
        // Setup
        $this->setUpSut();

        // Execute
        $result = $this->sut->__invoke($this->serviceManager, null);

        // Assert
        $this->assertInstanceOf(ExpiredPasswordController::class, $result);
    }

    protected function setUpSut(): void
    {
        $this->sut = new ExpiredPasswordControllerFactory();
    }

    protected function setUpConfig(array $config = []): array
    {
        if (!$this->serviceManager->has('Config') || $config !== []) {
            if ($config === []) {
                $config = self::CONFIG_VALID;
            }

            $this->serviceManager->setService('Config', $config);
        }

        return $this->serviceManager->get('Config');
    }

    protected function setUpDefaultServices(ServiceManager $serviceManager): ServiceManager|array
    {
        $serviceManager->setService(CommandSender::class, $this->setUpMockService(CommandSender::class));
        $serviceManager->setService(FormHelperService::class, $this->setUpMockService(FormHelperService::class));
        $serviceManager->setService(FlashMessengerHelperService::class, $this->setUpMockService(FlashMessengerHelperService::class));
        $serviceManager->setService(Session::class, $this->setUpMockService(Session::class));

        $this->setUpConfig();
        return $serviceManager;
    }

    protected function setUp(): void
    {
        unset($this->sut);
        $this->setUpServiceManager();
    }
}
