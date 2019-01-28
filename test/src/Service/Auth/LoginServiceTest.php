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
use Zend\Http\Request;
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

    /**
     * @var m\Mock
     */
    private $request;

    public function setUp()
    {
        $this->cookie = m::mock();
        $this->redirect = m::mock();
        $this->request = m::mock(Request::class);

        $sm = m::mock(ServiceManager::class)->makePartial();
        $sm->setService('Auth\CookieService', $this->cookie);
        $sm->setService('ControllerPluginManager', $sm);
        $sm->setService('Request', $this->request);
        $sm->setService('redirect', $this->redirect);

        $this->sut = new LoginService();
        $this->sut->createService($sm);
    }

    public function testLogin()
    {
        $tokenId = 'some-token';
        $response = m::mock(Response::class);

        $this->cookie->shouldReceive('createTokenCookie')
            ->with($response, 'some-token', false);

        $this->redirect->shouldReceive('toUrl')->with('/')->andReturn('REDIRECT');
        $this->request->shouldReceive('getQuery')->with('goto', false)->once()->andReturn(false);
        $this->request->shouldReceive('getUri->getScheme')->with()->once()->andReturn('https');

        $this->assertEquals('REDIRECT', $this->sut->login($tokenId, $response));
    }

    /**
     * @dataProvider dataProviderTestLoginGotoUrl
     *
     * @param bool   $expectedRedirectToGotoUrl Expect that should go to the gotoUrl
     * @param string $gotoUrl                   The Goto URL
     */
    public function testLoginGotoUrl($expectedRedirectToGotoUrl, $gotoUrl)
    {
        $tokenId = 'some-token';
        $response = m::mock(Response::class);

        $this->cookie->shouldReceive('createTokenCookie')->with($response, 'some-token', false)->once();

        $this->request->shouldReceive('getQuery')->with('goto', false)->once()->andReturn($gotoUrl);
        $this->request->shouldReceive('getUri->getScheme')->with()->atLeast()->times(1)->andReturn('https');
        $this->request->shouldReceive('getUri->getHost')->with()->once()->andReturn('foo.com');

        if ($expectedRedirectToGotoUrl) {
            $this->redirect->shouldReceive('toUrl')->with($gotoUrl)->andReturn('REDIRECT');
        } else {
            $this->redirect->shouldReceive('toUrl')->with('/')->andReturn('REDIRECT');
        }

        $this->assertEquals('REDIRECT', $this->sut->login($tokenId, $response));
    }

    public function dataProviderTestLoginGotoUrl()
    {
        return [
            'Wrong domain 1' => [false, 'http://fred.com/zzzz'],
            'Wrong domain 2' => [false, 'http://food.com/xxx'],
            'Wrong domain 3' => [false, 'https://www.foo.com/xxx'],
            'Wrong domain 4' => [false, 'https://www.foo.comd/xxx'],
            [true, 'https://foo.com/xxx'],
        ];
    }
}
