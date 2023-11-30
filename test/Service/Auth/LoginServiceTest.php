<?php

/**
 * Login Service Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Auth\Service\Auth;

use Common\Service\User\LastLoginService;
use DateTimeImmutable;
use Dvsa\Olcs\Auth\Service\Auth\LoginService;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Laminas\Http\Header\SetCookie;
use Laminas\Http\Request;
use Laminas\Http\Response;
use Laminas\ServiceManager\ServiceManager;

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

    /**
     * @var array|m\LegacyMockInterface|m\MockInterface
     */
    private $cookie;

    /**
     * @var array|m\LegacyMockInterface|m\MockInterface
     */
    private $redirect;

    /**
     * @var LastLoginService|m\LegacyMockInterface|m\MockInterface
     */
    private $lastLoginService;

    /**
     * @var m\Mock
     */
    private $request;

    public function setUp(): void
    {
        $this->cookie = m::mock();
        $this->redirect = m::mock();
        $this->request = m::mock(Request::class);
        $this->lastLoginService = m::mock(LastLoginService::class);

        $sm = m::mock(ServiceManager::class)->makePartial();
        $sm->setService('Auth\CookieService', $this->cookie);
        $sm->setService('ControllerPluginManager', $sm);
        $sm->setService('Common\Service\User\LastLoginService', $this->lastLoginService);
        $sm->setService('Request', $this->request);
        $sm->setService('redirect', $this->redirect);

        $this->sut = new LoginService();
        $this->sut->createService($sm);
    }

    public function testLogin()
    {
        $tokenId = 'some-token';
        $response = m::mock(Response::class);

        $this->lastLoginService->shouldReceive('updateLastLogin')->once()->andReturns();

        $this->cookie->shouldReceive('createTokenCookie')
            ->with($response, 'some-token', false);

        $this->request->shouldReceive('getQuery')->with('goto', false)->once()->andReturn(false);
        $this->request->shouldReceive('getUri->getScheme')->with()->once()->andReturn('https');

        $this->assertEquals('/', $this->sut->login($tokenId, $response));
    }

    /**
     * @dataProvider dataProviderTestLoginGotoUrl
     *
     * @param bool   $expectedGotoUrl Expect that should go to the gotoUrl
     * @param string $gotoUrl                   The Goto URL
     */
    public function testLoginGotoUrl($expectedGotoUrl, $gotoUrl)
    {
        $tokenId = 'some-token';
        $response = m::mock(Response::class);

        $this->lastLoginService->shouldReceive('updateLastLogin')->once()->andReturns();

        $this->cookie->shouldReceive('createTokenCookie')->with($response, 'some-token', false)->once();

        $this->request->shouldReceive('getQuery')->with('goto', false)->once()->andReturn($gotoUrl);
        $this->request->shouldReceive('getUri->getScheme')->with()->atLeast()->times(1)->andReturn('https');
        $this->request->shouldReceive('getUri->getHost')->with()->once()->andReturn('foo.com');

        $this->assertEquals($expectedGotoUrl, $this->sut->login($tokenId, $response));
    }

    public function testLoginToDocumentStoreSetsCookieExpiryToHour()
    {
        $tokenId = 'some-token';
        $gotoUrl = "https://foo.com/ms-ofba-authentication-successful";

        $response = m::mock(Response::class);

        $this->lastLoginService->shouldReceive('updateLastLogin')->once()->andReturns();

        $this->cookie->shouldReceive('createTokenCookie')->with($response, 'some-token', true)->once();

        $response->shouldReceive('getHeaders->addHeader')
            ->with(m::type(SetCookie::class))
            ->andReturnUsing(
                function (SetCookie $setCookie) {
                    $midnight = (new DateTimeImmutable('tomorrow'))->setTime(0, 0, 0);

                    //Date Format pasted from SetCookie::getExpires. It isn't the same as DateTime::COOKIE.
                    $midnightString = $midnight->format('D, d-M-Y H:i:s \G\M\T');

                    $this->assertEquals('secureToken', $setCookie->getName());
                    $this->assertEquals('.olcs.com', $setCookie->getDomain());
                    $this->assertEquals('some-token', $setCookie->getValue());
                    $this->assertEquals('/', $setCookie->getPath());

                    $this->assertEquals($midnightString, $setCookie->getExpires());
                    $this->assertFalse($setCookie->isSecure());
                    $this->assertTrue($setCookie->isHttponly());
                }
            );

        $this->request->shouldReceive('getQuery')->with('goto', false)->once()->andReturn($gotoUrl);
        $this->request->shouldReceive('getUri->getScheme')->with()->atLeast()->times(1)->andReturn('https');
        $this->request->shouldReceive('getUri->getHost')->with()->once()->andReturn('foo.com');

        $this->assertEquals($gotoUrl, $this->sut->login($tokenId, $response));
    }

    public function dataProviderTestLoginGotoUrl()
    {
        return [
            'Wrong domain 1' => ['/', 'http://fred.com/zzzz'],
            'Wrong domain 2' => ['/', 'http://food.com/xxx'],
            'Wrong domain 3' => ['/', 'https://www.foo.com/xxx'],
            'Wrong domain 4' => ['/', 'https://www.foo.comd/xxx'],
            'Right domain'   => ['https://foo.com/xxx', 'https://foo.com/xxx'],
        ];
    }
}
