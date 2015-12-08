<?php

/**
 * Login Service Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Dvsa\OlcsTest\Auth\Service\Auth;

use Dvsa\Olcs\Auth\Service\Auth\LoginService;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Zend\Http\Response;
use Zend\ServiceManager\ServiceManager;

/**
 * Login Service Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class LoginServiceTest extends MockeryTestCase
{
    /**
     * @var LoginService
     */
    private $sut;

    private $cookie;

    private $redirect;

    public function setUp()
    {
        $this->cookie = m::mock();
        $this->redirect = m::mock();

        $sm = m::mock(ServiceManager::class)->makePartial();
        $sm->setService('Auth\CookieService', $this->cookie);
        $sm->setService('ControllerPluginManager', $sm);
        $sm->setService('redirect', $this->redirect);

        $this->sut = new LoginService();
        $this->sut->createService($sm);
    }

    public function testLogin()
    {
        $tokenId = 'some-token';
        $response = m::mock(Response::class);
        $goto = null;

        $this->cookie->shouldReceive('createTokenCookie')
            ->with($response, 'some-token');

        $this->redirect->shouldReceive('toUrl')->with('/')->andReturn('REDIRECT');

        $this->assertEquals('REDIRECT', $this->sut->login($tokenId, $response, $goto));
    }

    public function testLoginGoto()
    {
        $tokenId = 'some-token';
        $response = m::mock(Response::class);
        $goto = '/foo';

        $this->cookie->shouldReceive('createTokenCookie')
            ->with($response, 'some-token');

        $this->redirect->shouldReceive('toUrl')->with('/foo')->andReturn('REDIRECT');

        $this->assertEquals('REDIRECT', $this->sut->login($tokenId, $response, $goto));
    }
}
