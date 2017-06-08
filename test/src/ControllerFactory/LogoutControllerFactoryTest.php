<?php
namespace Dvsa\OlcsTest\Auth\ControllerFactory;

use Dvsa\Olcs\Auth\Service\Auth\CookieService;
use Dvsa\Olcs\Auth\Service\Auth\LogoutService;
use Dvsa\OlcsTest\Auth\Bootstrap;
use Dvsa\Olcs\Auth\Controller\LogoutController;
use Zend\Mvc\Controller\ControllerManager;
use Dvsa\Olcs\Auth\ControllerFactory\LogoutControllerFactory;
use Zend\Http\PhpEnvironment\Request;
use Zend\ServiceManager\ServiceManager;

/**
 * Class ControllerFactoryTest
 * @covers \Dvsa\Olcs\Auth\ControllerFactory\LogoutControllerFactory
 */
class LogoutControllerFactoryTest extends \PHPUnit_Framework_TestCase
{
    /** @var ServiceManager */
    private $serviceManager;

    public function setUp()
    {
        // Get service manager
        $serviceManager = Bootstrap::getServiceManager();
        $serviceManager->setAllowOverride(true);

        // Mock coockie service
        $cookieService = $this->getMock(CookieService::class, [], [], '', false);
        $serviceManager->setService('Auth\CookieService', $cookieService);

        // Mock logout service
        $logoutService = $this->getMock(LogoutService::class, [], [], '', false);
        $serviceManager->setService('Auth\LogoutService', $logoutService);

        // Set realm by default, but can be overwritten
        $mockRequest = $this->getMock(Request::class, [], [], '', false);
        $mockRequest->method('getServer')->with('HTTP_X_REALM')->willReturn('test');
        $serviceManager->setService('request', $mockRequest);

        $this->serviceManager = $serviceManager;
    }

    /**
     * @param string $realm             Realm name
     * @param bool   $expectedException Are we expecting an exception?
     *
     * @dataProvider realmDataProvider
     */
    public function testLogoutControllerFactoryWithNoRealm(
        $realm,
        $expectedException = false
    ) {
        if ($expectedException === true) {
            $this->setExpectedException(\InvalidArgumentException::class, 'Realm is not specified');
        }

        // Set realm defined in data provider
        $mockRequest = $this->getMock(Request::class, [], [], '', false);
        $mockRequest->method('getServer')->with('HTTP_X_REALM')->willReturn($realm);
        $this->serviceManager->setService('request', $mockRequest);

        // Create controller config
        $controllerConfig = new \Zend\Servicemanager\Config(Bootstrap::getConfig());
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

    /**
     * 1. Realm
     * 2. Exception expected?
     *
     * @return array
     */
    public function realmDataProvider()
    {
        return [
            'No realm specified' => [
                '',
                true
            ],
            'any realm specified' => [
                'test',
                false,
            ],
        ];
    }

    public function testNoSelfServeLogoutUrlSpecified()
    {
        $this->setExpectedException(
            \InvalidArgumentException::class,
            'Selfserve logout redirect is not available in config'
        );

        // Remove the redirect URL from config in this mock
        $config = $this->serviceManager->get('config');
        unset($config['selfserve_logout_redirect_url']);
        $this->serviceManager->setService('config', $config);

        // Create controller config
        $controllerConfig = new \Zend\Servicemanager\Config(Bootstrap::getConfig());
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