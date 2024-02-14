<?php

namespace Dvsa\OlcsTest\Auth\Service\Auth;

use Dvsa\Olcs\Auth\Service\Auth\LogoutService;
use Psr\Container\ContainerInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Laminas\Http\Headers;
use Laminas\Http\Response;
use Laminas\ServiceManager\ServiceManager;

class LogoutServiceTest extends MockeryTestCase
{
    /**
     * @var LogoutService
     */
    private $sut;

    private $client;

    private $cookie;

    private $responseDecoder;

    public function setUp(): void
    {
        $this->cookie = m::mock();
        $this->client = m::mock();
        $this->responseDecoder = m::mock();

        $container = m::mock(ContainerInterface::class);
        $container->expects('get')->with('Auth\CookieService')->andReturn($this->cookie);
        $container->expects('get')->with('Auth\Client')->andReturn($this->client);
        $container->expects('get')->with('Auth\ResponseDecoderService')->andReturn($this->responseDecoder);

        $this->sut = new LogoutService();
        $this->sut->__invoke($container, LogoutService::class);
    }

    public function testLogout()
    {
        $tokenId = 'some-token';

        $this->cookie->shouldReceive('getCookieName')
            ->andReturn('cookie-name');

        $this->client->shouldReceive('post')
            ->with('/json/sessions/?_action=logout', [], m::type(Headers::class))
            ->andReturnUsing(
                function ($url, $data, Headers $headers) {
                    $this->assertEquals('some-token', $headers->get('cookie-name')->getFieldValue());

                    $response = new Response();
                    $response->setStatusCode(200);
                    return $response;
                }
            );

        $this->assertTrue($this->sut->logout($tokenId));
    }
}
