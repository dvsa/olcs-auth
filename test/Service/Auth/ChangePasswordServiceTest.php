<?php

/**
 * Change Password Service Test
 */

namespace Dvsa\OlcsTest\Auth\Service\Auth;

use Dvsa\Olcs\Auth\Service\Auth\ChangePasswordService;
use Dvsa\Olcs\Auth\Service\Auth\ResponseDecoderService;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Laminas\Http\Headers;
use Laminas\Http\Request;
use Laminas\Http\Response;
use Laminas\ServiceManager\ServiceManager;

/**
 * Change Password Service Test
 */
class ChangePasswordServiceTest extends MockeryTestCase
{
    /**
     * @var ChangePasswordService
     */
    private $sut;

    private $cookie;

    private $client;

    private $responseDecoder;

    public function setUp(): void
    {
        $this->cookie = m::mock();
        $this->client = m::mock();
        $this->responseDecoder = new ResponseDecoderService();

        $sm = m::mock(ServiceManager::class)->makePartial();
        $sm->setService('Auth\CookieService', $this->cookie);
        $sm->setService('Auth\Client', $this->client);
        $sm->setService('Auth\ResponseDecoderService', $this->responseDecoder);

        $this->sut = new ChangePasswordService();
        $this->sut->createService($sm);
    }

    public function testUpdatePassword()
    {
        $data = [
            'currentpassword' => 'old-password',
            'userpassword' => 'new-password',
        ];

        $request = new Request();

        $this->cookie
            ->shouldReceive('getCookieName')->andReturn('cookie-name')
            ->shouldReceive('getCookie')->with($request)->andReturn('some-token');

        $this->client->shouldReceive('post')
            ->with('json/users/?_action=idFromSession', [], m::type(Headers::class))
            ->andReturnUsing(
                function ($url, $data, Headers $headers) {
                    $this->assertEquals('some-token', $headers->get('cookie-name')->getFieldValue());

                    $response = new Response();
                    $response->setStatusCode(200);
                    $response->setContent('{"id": "my-username"}');
                    return $response;
                }
            );

        $this->client->shouldReceive('post')
            ->with('json/users/my-username?_action=changePassword', $data, m::type(Headers::class))
            ->andReturnUsing(
                function ($url, $data, Headers $headers) {
                    $this->assertEquals('some-token', $headers->get('cookie-name')->getFieldValue());

                    $response = new Response();
                    $response->setStatusCode(200);
                    $response->setContent('{}');
                    return $response;
                }
            );

        $result = $this->sut->updatePassword($request, 'old-password', 'new-password');

        $expected = [
            'status' => 200
        ];

        $this->assertEquals($expected, $result);
    }
}
