<?php

namespace OlcsTest\Logging\src\ControllerFactory;

use Common\Rbac\JWTIdentityProvider;
use Dvsa\Olcs\Auth\Service\Auth\CookieService;
use Dvsa\Olcs\Auth\Service\Auth\LogoutService;
use Dvsa\Olcs\Auth\Controller\LogoutController;
use Dvsa\OlcsTest\Auth\Bootstrap;
use Laminas\Mvc\Controller\ControllerManager;
use Dvsa\Olcs\Auth\ControllerFactory\LogoutControllerFactory;
use Laminas\Http\PhpEnvironment\Request;
use Laminas\ServiceManager\Config;
use Laminas\ServiceManager\ServiceManager;
use Mockery as m;

/**
 * Class ControllerFactoryTest
 * @covers \Dvsa\Olcs\Auth\ControllerFactory\LogoutControllerFactory
 */
class LogoutControllerFactoryTest extends \PHPUnit\Framework\TestCase
{
    const CONFIG_VALID = [
        'auth' => [
            'identity_provider' => JWTIdentityProvider::class
        ]
    ];

    private $serviceManager;

    public function setUp(): void
    {
        // Mock coockie service
        $cookieService = $this->createMock(CookieService::class);

        $this->serviceManager = m::mock(ServiceManager::class)->makePartial();
        if ($this->serviceManager !== null) {
            $this->serviceManager->setService('Auth\CookieService', $cookieService);
        }

        // Mock logout service
        $logoutService = $this->createMock(LogoutService::class);
        $this->serviceManager->setService('Auth\LogoutService', $logoutService);

        // Set realm by default, but can be overwritten
        $mockRequest = $this->createMock(Request::class);
        $mockRequest->method('getServer')->with('HTTP_X_REALM')->willReturn('test');
        $this->serviceManager->setService('request', $mockRequest);

        //$this->serviceManager = $serviceManager;
    }

    /**
     * @param string $realm             Realm name
     * @param bool   $expectedException Are we expecting an exception?
     *
     * @dataProvider realmDataProvider
     */
    public function testLogoutControllerFactoryWithRealm($realm)
    {
        $config = [];
        // Set realm defined in data provider
        $mockRequest = $this->createMock(Request::class);
        $mockRequest->method('getServer')->with('HTTP_X_REALM')->willReturn($realm);

        if (!$this->serviceManager->has('Config') || !empty($config)) {
            if (empty($config)) {
                $config = static::CONFIG_VALID;
            }
            $this->serviceManager->setService('Config', $config);
        }

        $config = $this->serviceManager->get('config');
        $config['auth'] = ['session_name' => 'session_name', 'identity_provider' => 'identity_provider'];

        // Create controller config
        $controllerConfig = new Config(Bootstrap::getConfig());
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
        $controllerConfig = new Config(Bootstrap::getConfig());
        $controllerManager = new ControllerManager($controllerConfig);

        $controllerManager->setServiceLocator($this->serviceManager);

        // Initialise Factory
        $factory = new LogoutControllerFactory();

        // Assert factory returns controller unless exception expected
        self::assertInstanceOf(
            LogoutController::class,
            $factory->createService($controllerManager)
        );
    }
}
