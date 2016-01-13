<?php

/**
 * Forgot Password Service Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Dvsa\OlcsTest\Auth\Service\Auth;

use Dvsa\Olcs\Auth\Service\Auth\ForgotPasswordService;
use Dvsa\Olcs\Auth\Service\Auth\ResponseDecoderService;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Zend\Http\Response;
use Zend\ServiceManager\ServiceManager;

/**
 * Forgot Password Service Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ForgotPasswordServiceTest extends MockeryTestCase
{
    /**
     * @var ForgotPasswordService
     */
    private $sut;

    private $client;

    private $responseDecoder;

    public function setUp()
    {
        $this->client = m::mock();
        $this->responseDecoder = new ResponseDecoderService();
        $translator = m::mock();
        $translator->shouldReceive('translate')->andReturnUsing(
            function ($in) {
                return $in . '-translated';
            }
        );

        $sm = m::mock(ServiceManager::class)->makePartial();
        $sm->setService('Auth\Client', $this->client);
        $sm->setService('Auth\ResponseDecoderService', $this->responseDecoder);
        $sm->setService('Translator', $translator);

        $this->sut = new ForgotPasswordService();
        $this->sut->createService($sm);
    }

    public function testForgotPassword()
    {
        $data = [
            'username' => 'bob',
            'subject' => 'auth.forgot-password.email.subject-translated',
            'message' => 'auth.forgot-password.email.message-translated'
        ];

        $response = new Response();
        $response->setStatusCode(200);
        $response->setContent('{"foo": "bar"}');

        $this->client->shouldReceive('post')
            ->with('json/users/?_action=forgotPassword', $data, null)
            ->andReturn($response);

        $result = $this->sut->forgotPassword('bob');

        $expected = [
            'foo' => 'bar',
            'status' => 200
        ];

        $this->assertEquals($expected, $result);
    }
}
