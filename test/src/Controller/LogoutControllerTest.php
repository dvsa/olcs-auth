<?php

namespace Dvsa\OlcsTest\Auth\Controller;

use Dvsa\Olcs\Auth\Controller\LogoutController;
use Dvsa\Olcs\Auth\Service\Auth\CookieService;
use Dvsa\Olcs\Auth\Service\Auth\LogoutService;
use Zend\Mvc\Controller\Plugin\Redirect;
use Zend\Mvc\Controller\PluginManager;
use Zend\Di\ServiceLocatorInterface;
use Dvsa\OlcsTest\Auth\Bootstrap;
use Mockery as m;

/**
 * Change Password Controller Test
 */
class LogoutControllerTest extends \PHPunit\Framework\TestCase
{
    const REDIRECT_URL = 'http://www.example-gov-site.uk';

    /**
     * @var Redirect
     */
    private $redirect;

    /**
     * @var ServiceLocatorInterface
     */
    private $serviceManager;

    public function setUp()
    {
        $this->serviceManager = Bootstrap::getServiceManager();
        $this->serviceManager->setAllowOverride(true);

        // Mock coockie service
        $cookieService = $this->createMock(CookieService::class);
        $cookieService->method('getCookie')->willReturn('test');
        $this->serviceManager->setService('Auth\CookieService', $cookieService);

        // Mock logout service
        $logoutService = $this->createMock(LogoutService::class);
        $this->serviceManager->setService('Auth\LogoutService', $logoutService);
    }

    public function testIsRealmSelfServeThenRedirectToGovSite()
    {
        $controller = new LogoutController(
            $this->serviceManager->get('request'),
            $this->serviceManager->get('response'),
            $this->serviceManager->get('Auth\CookieService'),
            $this->serviceManager->get('Auth\LogoutService'),
            true,
            self::REDIRECT_URL
        );

        // Mock redirect service
        $pm = m::mock(PluginManager::class)->makePartial();
        $this->redirect = m::mock(Redirect::class)->makePartial();
        $pm->setService('redirect', $this->redirect);
        $controller->setPluginManager($pm);

        $this->redirect->shouldReceive('toUrl')
            ->with(self::REDIRECT_URL)
            ->andReturn('REDIRECT');

        $this->assertEquals('REDIRECT', $controller->indexAction());
    }

    public function testIsRealmNotSelfServeThenRedirectToLogin()
    {
        $controller = new LogoutController(
            $this->serviceManager->get('request'),
            $this->serviceManager->get('response'),
            $this->serviceManager->get('Auth\CookieService'),
            $this->serviceManager->get('Auth\LogoutService'),
            false,
            self::REDIRECT_URL
        );

        // Mock redirect service
        $pm = m::mock(PluginManager::class)->makePartial();
        $this->redirect = m::mock(Redirect::class)->makePartial();
        $pm->setService('redirect', $this->redirect);
        $controller->setPluginManager($pm);

        $this->redirect->shouldReceive('toRoute')
            ->with('auth/login')
            ->andReturn('REDIRECT');

        $this->assertEquals('REDIRECT', $controller->indexAction());
    }
}
