<?php

namespace Dvsa\OlcsTest\Auth\ControllerFactory;

use Dvsa\Olcs\Auth\Service\Auth\CookieService;
use Dvsa\Olcs\Auth\Service\Auth\LogoutService;
use Dvsa\OlcsTest\Auth\Bootstrap;
use Dvsa\Olcs\Auth\Controller\LogoutController;
use Laminas\Mvc\Controller\ControllerManager;
use Dvsa\Olcs\Auth\ControllerFactory\LogoutControllerFactory;
use Laminas\Http\PhpEnvironment\Request;
use Olcs\TestHelpers\Service\MocksServicesTrait;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Class ControllerFactoryTest
 * @covers \Dvsa\Olcs\Auth\ControllerFactory\LogoutControllerFactory
 */
class LogoutControllerFactoryTest extends MockeryTestCase
{
    use MocksServicesTrait;

    const CONFIG_VALID = [
        'selfserve_logout_redirect_url' => 'selfserve_logout_redirect_url',
        'auth' => [
            'identity_provider' => JWTIdentityProvider::class,
            'session_name' => 'session_name',
            'identity_provider' => 'identity_provider'
        ]
    ];

    public function setUp(): void
    {
        $this->serviceManager = $this->setUpServiceManager();
        $this->setUpConfig();

        // Mock cookie service
        $cookieService = $this->createMock(CookieService::class);
        $this->serviceManager->setService('Auth\CookieService', $cookieService);

        // Mock logout service
        $logoutService = $this->createMock(LogoutService::class);
        $this->serviceManager->setService('Auth\LogoutService', $logoutService);

        // Set realm by default, but can be overwritten
        $mockRequest = $this->createMock(Request::class);
        $mockRequest->method('getServer')->with('HTTP_X_REALM')->willReturn('test');
        $this->serviceManager->setService('request', $mockRequest);
    }

    protected function setUpConfig(array $config = []): array
    {
        if (!$this->serviceManager->has('Config') || !empty($config)) {
            if (empty($config)) {
                $config = static::CONFIG_VALID;
            }
            $this->serviceManager->setService('Config', $config);
        }
        return $this->serviceManager->get('Config');
    }

    /**
     * @param string $realm             Realm name
     * @param bool   $expectedException Are we expecting an exception?
     *
     * @dataProvider realmDataProvider
     */
    public function testLogoutControllerFactoryWithRealm($realm)
    {
        // Set realm defined in data provider
        $mockRequest = $this->createMock(Request::class);
        $mockRequest->method('getServer')->with('HTTP_X_REALM')->willReturn($realm);
        $this->serviceManager->setService('request', $mockRequest);

        $config = $this->serviceManager->get('config');
        $this->serviceManager->setService('config', $config);

        // Create controller config
        $controllerConfig = new \Laminas\ServiceManager\Config(Bootstrap::getConfig());
        $controllerManager = new ControllerManager($controllerConfig);

        $controllerManager->setServiceLocator($this->serviceManager);

        // Initialise Factory
        $factory = new LogoutControllerFactory();

        // Assert factory returns controller unless exception expected
        self::assertInstanceOf(
            LogoutController::class,
            $factory->__invoke($controllerManager, LogoutController::class)
        );
    }

    /**
     * 1. Realm
     *
     * @return array
     */
    public function realmDataProvider()
    {
        return [
            'No realm specified' => [
                '',
            ],
            'any realm specified' => [
                'test',
            ],
            'self serve' => [
                'selfserve',
            ],
        ];
    }

    public function testNoSelfServeLogoutUrlSpecified()
    {
        $this->expectException(
            \InvalidArgumentException::class,
            'Selfserve logout redirect is not available in config'
        );

        // Remove the redirect URL from config in this mock
        $config = $this->serviceManager->get('config');
        unset($config['selfserve_logout_redirect_url']);
        $this->serviceManager->setService('config', $config);

        // Create controller config
        $controllerConfig = new \Laminas\ServiceManager\Config(Bootstrap::getConfig());
        $controllerManager = new ControllerManager($controllerConfig);

        $controllerManager->setServiceLocator($this->serviceManager);

        // Initialise Factory
        $factory = new LogoutControllerFactory();

        // Assert factory returns controller unless exception expected
        self::assertInstanceOf(
            LogoutController::class,
            $factory->__invoke($controllerManager, null)
        );
    }
}
