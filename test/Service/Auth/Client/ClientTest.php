<?php

/**
 * Client Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Auth\Service\Auth\Client;

use Dvsa\Olcs\Auth\Service\Auth\Client\Client;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Laminas\Http\Client\Adapter\Test;
use Laminas\Http\Headers;
use Laminas\Http\Response;
use Dvsa\Olcs\Auth\Service\Auth\Exception;
use Laminas\ServiceManager\ServiceManager;

/**
 * Client Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ClientTest extends MockeryTestCase
{
    /**
     * @var Client
     */
    private $sut;

    private $uriBuilder;

    /**
     * @var Test
     */
    private $adapter;

    public function setUp(): void
    {
        $this->uriBuilder = m::mock();
        $this->adapter = new Test();

        $config = [
            'openam' => [
                'client' => [
                    'options' => [
                        'adapter' => $this->adapter,
                        'timeout' => 10
                    ]
                ]
            ]
        ];

        $sm = m::mock(ServiceManager::class)->makePartial();
        $sm->setService('Auth\Client\UriBuilder', $this->uriBuilder);
        $sm->setService('Config', $config);

        $this->sut = new Client();
        $this->sut->createService($sm);
    }

    public function testPost()
    {
        $uri = 'http://hostname:123/foo/bar';

        $this->uriBuilder->shouldReceive('build')->with($uri)->andReturn($uri);
        $response = $this->sut->post($uri, ['foo' => 'bar']);
        $this->assertInstanceOf(Response::class, $response);

        $request = $this->sut->getLastRawRequest();

        $expected = implode(
            "\r\n",
            [
                'POST /foo/bar HTTP/1.1',
                'Host: hostname:123',
                'Connection: close',
                'Accept-Encoding: gzip, deflate',
                'User-Agent: Laminas\Http\Client',
                'Content-Type: application/json',
                'Content-Length: 13',
                '',
                '{"foo":"bar"}'
            ]
        );

        $this->assertEquals($expected, $request);
    }

    public function testPostWithHeaders()
    {
        $uri = 'http://hostname:123/foo/bar';

        $headers = new Headers();
        $headers->addHeaderLine('X-Username', 'bob');
        $headers->addHeaderLine('X-Password', 'password');

        $this->uriBuilder->shouldReceive('build')->with($uri)->andReturn($uri);
        $response = $this->sut->post($uri, ['foo' => 'bar'], $headers);
        $this->assertInstanceOf(Response::class, $response);

        $request = $this->sut->getLastRawRequest();

        $expected = implode(
            "\r\n",
            [
                'POST /foo/bar HTTP/1.1',
                'Host: hostname:123',
                'Connection: close',
                'Accept-Encoding: gzip, deflate',
                'User-Agent: Laminas\Http\Client',
                'Content-Type: application/json',
                'Content-Length: 13',
                'X-Username: bob',
                'X-Password: password',
                '',
                '{"foo":"bar"}'
            ]
        );

        $this->assertEquals($expected, $request);
    }

    public function testPostBadJson()
    {
        $this->expectException(Exception\RuntimeException::class);

        $uri = 'http://hostname:123/foo/bar';

        $this->uriBuilder->shouldReceive('build')->with($uri)->andReturn($uri);
        $response = $this->sut->post($uri, ['foo' => "foo\x92"]);
        $this->assertInstanceOf(Response::class, $response);
    }
}
