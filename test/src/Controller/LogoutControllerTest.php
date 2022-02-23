<?php

namespace Dvsa\OlcsTest\Auth\Controller;

use Dvsa\Olcs\Auth\Controller\LogoutController;
use Dvsa\Olcs\Auth\Service\Auth\CookieService;
use Dvsa\Olcs\Auth\Service\Auth\LogoutService;
use Laminas\Mvc\Controller\Plugin\Redirect;
use Laminas\Mvc\Controller\PluginManager;
use Laminas\Di\ServiceLocatorInterface;
use Dvsa\OlcsTest\Auth\Bootstrap;
use Laminas\Session\Container;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Change Password Controller Test
 */
class LogoutControllerTest extends MockeryTestCase
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

    public function setUp(): void
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
            self::REDIRECT_URL,
            $this->createMock(Container::class),
            true
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
            self::REDIRECT_URL,
            $this->createMock(Container::class),
            true
        );

        // Mock redirect service
        $pm = m::mock(PluginManager::class)->makePartial();
        $this->redirect = m::mock(Redirect::class)->makePartial();
        $pm->setService('redirect', $this->redirect);
        $controller->setPluginManager($pm);

        $this->redirect->shouldReceive('toRoute')
            ->with('auth/login/GET')
            ->andReturn('REDIRECT');

        $this->assertEquals('REDIRECT', $controller->indexAction());
    }

    public function testWhenOpenAmEnabledWeCallCookieAndLogoutService()
    {
        $mockCookieService = m::mock(CookieService::class);
        $mockCookieService->expects('getCookie')->andReturn('token');
        $mockCookieService->expects('destroyCookie')->andReturn();

        $mockLogoutService = m::mock(LogoutService::class);
        $mockLogoutService->expects('logout')->andReturn();

        $controller = new LogoutController(
            $this->serviceManager->get('request'),
            $this->serviceManager->get('response'),
            $mockCookieService,
            $mockLogoutService,
            false,
            self::REDIRECT_URL,
            $this->createMock(Container::class),
            true
        );

        // Mock redirect service
        $pm = m::mock(PluginManager::class)->makePartial();
        $this->redirect = m::mock(Redirect::class)->makePartial();
        $pm->setService('redirect', $this->redirect);
        $controller->setPluginManager($pm);

        $this->redirect->shouldReceive('toRoute')
            ->with('auth/login/GET')
            ->andReturn('REDIRECT');

        $this->assertEquals('REDIRECT', $controller->indexAction());
    }

    public function testWhenOpenAmDisabledWeDoNotCallCookieAndLogoutService()
    {
        $mockCookieService = m::mock(CookieService::class);
        $mockCookieService->shouldNotReceive('getCookie');
        $mockCookieService->shouldNotReceive('destroyCookie');

        $mockLogoutService = m::mock(LogoutService::class);
        $mockLogoutService->shouldNotReceive('logout');

        $controller = new LogoutController(
            $this->serviceManager->get('request'),
            $this->serviceManager->get('response'),
            $mockCookieService,
            $mockLogoutService,
            false,
            self::REDIRECT_URL,
            $this->createMock(Container::class),
            false
        );

        // Mock redirect service
        $pm = m::mock(PluginManager::class)->makePartial();
        $this->redirect = m::mock(Redirect::class)->makePartial();
        $pm->setService('redirect', $this->redirect);
        $controller->setPluginManager($pm);

        $this->redirect->shouldReceive('toRoute')
            ->with('auth/login/GET')
            ->andReturn('REDIRECT');

        $this->assertEquals('REDIRECT', $controller->indexAction());
    }
}
