<?php

/**
 * Cookie Service Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Dvsa\OlcsTest\Auth\Service\Auth;

use Dvsa\Olcs\Auth\Service\Auth\CookieService;
use Dvsa\Olcs\Auth\Service\Auth\Exception\RuntimeException;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Zend\Http\Header\SetCookie;
use Zend\Http\Request;
use Zend\Http\Response;
use Zend\ServiceManager\ServiceManager;

/**
 * Cookie Service Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class CookieServiceTest extends MockeryTestCase
{
    /**
     * @var CookieService
     */
    private $sut;

    public function setUp()
    {
        $request = m::mock();
        $request->shouldReceive('getUri->getHost')->andReturn('foo.olcs.com');

        $config = [
            'openam' => [
                'cookie' => [
                    'name' => 'secureToken',
                    'domain' => '.olcs.com'
                ]
            ]
        ];

        $sm = m::mock(ServiceManager::class)->makePartial();
        $sm->setService('Config', $config);
        $sm->setService('Request', $request);

        $this->sut = new CookieService();
        $this->sut->createService($sm);
    }

    public function testCreateService()
    {
        $request = m::mock();

        $this->setExpectedException(RuntimeException::class);

        $sm = m::mock(ServiceManager::class)->makePartial();
        $sm->setService('Config', []);
        $sm->setService('Request', $request);

        $sut = new CookieService();
        $sut->createService($sm);
    }

    public function testCreateTokenCookie()
    {
        $response = m::mock(Response::class);
        $response->shouldReceive('getHeaders->addHeader')
            ->with(m::type(SetCookie::class))
            ->andReturnUsing(
                function (SetCookie $setCookie) {
                    $this->assertEquals('secureToken', $setCookie->getName());
                    $this->assertEquals('.olcs.com', $setCookie->getDomain());
                    $this->assertEquals('some-token', $setCookie->getValue());
                    $this->assertEquals('/', $setCookie->getPath());
                    $this->assertEquals(null, $setCookie->getExpires());
                    $this->assertFalse($setCookie->isSecure());
                    $this->assertTrue($setCookie->isHttponly());
                }
            );

        $this->sut->createTokenCookie($response, 'some-token');
    }

    public function testCreateTokenCookieWithoutMatchingHost()
    {
        $request = m::mock();
        $request->shouldReceive('getUri->getHost')->andReturn('foo.olcs.co.uk');

        $config = [
            'openam' => [
                'cookie' => [
                    'name' => 'secureToken',
                    'domain' => '.olcs.com'
                ]
            ]
        ];

        $sm = m::mock(ServiceManager::class)->makePartial();
        $sm->setService('Config', $config);
        $sm->setService('Request', $request);

        $this->sut = new CookieService();
        $this->sut->createService($sm);

        $response = m::mock(Response::class);
        $response->shouldReceive('getHeaders->addHeader')
            ->with(m::type(SetCookie::class))
            ->andReturnUsing(
                function (SetCookie $setCookie) {
                    $this->assertEquals('secureToken', $setCookie->getName());
                    $this->assertEquals(null, $setCookie->getDomain());
                    $this->assertEquals('some-token', $setCookie->getValue());
                    $this->assertEquals('/', $setCookie->getPath());
                    $this->assertEquals(null, $setCookie->getExpires());
                    $this->assertFalse($setCookie->isSecure());
                    $this->assertTrue($setCookie->isHttponly());
                }
            );

        $this->sut->createTokenCookie($response, 'some-token');
    }

    public function testDestroyCookie()
    {
        $response = m::mock(Response::class);
        $response->shouldReceive('getHeaders->addHeader')
            ->with(m::type(SetCookie::class))
            ->andReturnUsing(
                function (SetCookie $setCookie) {
                    $this->assertEquals('secureToken', $setCookie->getName());
                    $this->assertEquals('.olcs.com', $setCookie->getDomain());
                    $this->assertEquals(null, $setCookie->getValue());
                    $this->assertEquals('/', $setCookie->getPath());
                    $this->assertTrue(strtotime($setCookie->getExpires()) < time());
                }
            );

        $this->sut->destroyCookie($response);
    }

    public function testGetCookieNull()
    {
        $cookie = new \stdClass();

        $request = m::mock(Request::class);
        $request->shouldReceive('getHeaders->get')
            ->with('Cookie')
            ->andReturn($cookie);

        $this->assertNull($this->sut->getCookie($request));
    }

    public function testGetCookie()
    {
        $cookie = new \stdClass();
        $cookie->secureToken = 'foo';

        $request = m::mock(Request::class);
        $request->shouldReceive('getHeaders->get')
            ->with('Cookie')
            ->andReturn($cookie);

        $this->assertEquals('foo', $this->sut->getCookie($request));
    }

    public function testGetCookieName()
    {
        $this->assertEquals('secureToken', $this->sut->getCookieName());
    }
}
