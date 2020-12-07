<?php

/**
 * Reset Password Service Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Dvsa\OlcsTest\Auth\Service\Auth;

use Dvsa\Olcs\Auth\Service\Auth\ResetPasswordService;
use Dvsa\Olcs\Auth\Service\Auth\ResponseDecoderService;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Laminas\Http\Response;
use Laminas\ServiceManager\ServiceManager;

/**
 * Reset Password Service Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ResetPasswordServiceTest extends MockeryTestCase
{
    /**
     * @var ResetPasswordService
     */
    private $sut;

    private $client;

    private $responseDecoder;

    public function setUp(): void
    {
        $this->client = m::mock();
        $this->responseDecoder = new ResponseDecoderService();

        $sm = m::mock(ServiceManager::class)->makePartial();
        $sm->setService('Auth\Client', $this->client);
        $sm->setService('Auth\ResponseDecoderService', $this->responseDecoder);

        $this->sut = new ResetPasswordService();
        $this->sut->createService($sm);
    }

    public function testConfirm()
    {
        $data = [
            'username' => 'Bob',
            'tokenId' => 'token-id',
            'confirmationId' => 'conf-id'
        ];

        $response = new Response();
        $response->setStatusCode(200);
        $response->setContent('{"foo": "bar"}');

        $this->client->shouldReceive('post')
            ->with('json/users?_action=confirm', $data, null)
            ->andReturn($response);

        $result = $this->sut->confirm('Bob', 'conf-id', 'token-id');

        $expected = [
            'foo' => 'bar',
            'status' => 200
        ];

        $this->assertEquals($expected, $result);
    }

    public function testResetPassword()
    {
        $data = [
            'userpassword' => 'password',
            'username' => 'Bob',
            'tokenId' => 'token-id',
            'confirmationId' => 'conf-id'
        ];

        $response = new Response();
        $response->setStatusCode(200);
        $response->setContent('{"foo": "bar"}');

        $this->client->shouldReceive('post')
            ->with('json/users?_action=forgotPasswordReset', $data, null)
            ->andReturn($response);

        $result = $this->sut->resetPassword('Bob', 'conf-id', 'token-id', 'password');

        $expected = [
            'foo' => 'bar',
            'status' => 200
        ];

        $this->assertEquals($expected, $result);
    }
}
