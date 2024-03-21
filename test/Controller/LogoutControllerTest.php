<?php

namespace Dvsa\OlcsTest\Auth\Controller;

use Dvsa\Olcs\Auth\Controller\LogoutController;
use Laminas\Http\Request;
use Laminas\Http\Response;
use Laminas\Mvc\Controller\Plugin\Redirect;
use Laminas\Mvc\Controller\PluginManager;
use Laminas\Session\Container;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Change Password Controller Test
 */
class LogoutControllerTest extends MockeryTestCase
{
    private const REDIRECT_URL = 'http://www.example-gov-site.uk';

    /**
     * @var Redirect
     */
    private $redirect;

    /**
     * @var Request|(Request&m\LegacyMockInterface)|(Request&m\MockInterface)|m\LegacyMockInterface|m\MockInterface
     */
    private $request;

    /**
     * @var Response|(Response&m\LegacyMockInterface)|(Response&m\MockInterface)|m\LegacyMockInterface|m\MockInterface
     */
    private $response;

    public function testIsRealmSelfServeThenRedirectToGovSite(): void
    {
        $controller = new LogoutController(
            true,
            self::REDIRECT_URL,
            $this->createMock(Container::class)
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

    public function testIsRealmNotSelfServeThenRedirectToLogin(): void
    {
        $controller = new LogoutController(
            false,
            self::REDIRECT_URL,
            $this->createMock(Container::class)
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
