<?php

/**
 * Forgot Password Service Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Auth\Service\Auth;

use Common\Service\Cqrs\Exception;
use Common\Service\Cqrs\Exception\NotFoundException;
use Common\Service\Cqrs\Query\QuerySender;
use Dvsa\Olcs\Auth\Service\Auth\Client\Client;
use Dvsa\Olcs\Auth\Service\Auth\Exception\OpenAmResetPasswordFailedException;
use Dvsa\Olcs\Auth\Service\Auth\Exception\UserCannotResetPasswordException;
use Dvsa\Olcs\Auth\Service\Auth\Exception\UserNotFoundException;
use Dvsa\Olcs\Auth\Service\Auth\ForgotPasswordService;
use Dvsa\Olcs\Auth\Service\Auth\ResponseDecoderService;
use Dvsa\Olcs\Transfer\Query\User\Pid;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use ReflectionMethod;
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

    /**
     * @var Client|m\Mock
     */
    private $client;

    private $responseDecoder;

    /**
     * @var QuerySender|m\Mock
     */
    private $querySender;

    public function setUp()
    {
        $this->client = m::mock();
        $this->responseDecoder = new ResponseDecoderService();
        $this->querySender = m::mock(QuerySender::class);
        $translator = m::mock();
        $translator->shouldReceive('translate')->andReturnUsing(
            function ($in) {
                return $in . '-translated';
            }
        );

        /** @var ServiceManager|m\Mock $sm */
        $sm = m::mock(ServiceManager::class)->makePartial();
        $sm->setService('Auth\Client', $this->client);
        $sm->setService('Auth\ResponseDecoderService', $this->responseDecoder);
        $sm->setService('Translator', $translator);
        $sm->setService('QuerySender', $this->querySender);

        $this->sut = new ForgotPasswordService();
        $this->sut->createService($sm);
    }

    public function testForgotPasswordWhenUsernameNotFound()
    {
        $this->client->shouldNotReceive($this->getValidMethodName(Client::class, 'post'));

        $this->querySender->shouldReceive('send')
            ->with(\Hamcrest\Matchers::equalTo(Pid::create(['id' => 'bob'])))
            ->andThrow(NotFoundException::class);

        $this->expectException(UserNotFoundException::class);

        $this->sut->forgotPassword('bob');
    }

    public function testForgotPasswordWhenServiceCallIsNotOkay()
    {
        $this->client->shouldNotReceive($this->getValidMethodName(Client::class, 'post'));

        /** @var Response|m\Mock $cqrsResponse */
        $cqrsResponse = m::mock(Response::class);

        $this->querySender->shouldReceive('send')
            ->with(\Hamcrest\Matchers::equalTo(Pid::create(['id' => 'bob'])))
            ->andReturn($cqrsResponse);

        $cqrsResponse->shouldReceive('isOk')
            ->andReturn(false);
        $cqrsResponse->shouldReceive('getBody')
            ->andReturn('test response body');

        $this->expectException(Exception::class);
        $this->sut->forgotPassword('bob');
    }

    public function testForgotPasswordWhenServiceCallFails()
    {
        $this->client->shouldNotReceive($this->getValidMethodName(Client::class, 'post'));

        $expectedException = new Exception();
        $this->querySender->shouldReceive('send')
            ->with(\Hamcrest\Matchers::equalTo(Pid::create(['id' => 'bob'])))
            ->andThrow($expectedException);

        $this->expectException(Exception::class);

        try {
            $this->sut->forgotPassword('bob');
        } catch (Exception $exception) {
            $this->assertSame($expectedException, $exception);
            throw $exception;
        }
    }

    public function testForgotPasswordWhenCanResetPasswordMissing()
    {
        $this->client->shouldNotReceive($this->getValidMethodName(Client::class, 'post'));

        /** @var Response|m\Mock $cqrsResponse */
        $cqrsResponse = m::mock(Response::class);

        $this->querySender->shouldReceive('send')
            ->with(\Hamcrest\Matchers::equalTo(Pid::create(['id' => 'bob'])))
            ->andReturn($cqrsResponse);

        $cqrsResponse->shouldReceive('isOk')
            ->andReturn(true);
        $cqrsResponse->shouldReceive('getResult')
            ->andReturn([]);

        $this->expectException(UserCannotResetPasswordException::class);

        $this->sut->forgotPassword('bob');
    }

    public function testForgotPasswordWhenCanNotResetPassword()
    {
        $this->client->shouldNotReceive($this->getValidMethodName(Client::class, 'post'));

        /** @var Response|m\Mock $cqrsResponse */
        $cqrsResponse = m::mock(Response::class);

        $this->querySender->shouldReceive('send')
            ->with(\Hamcrest\Matchers::equalTo(Pid::create(['id' => 'bob'])))
            ->andReturn($cqrsResponse);

        $cqrsResponse->shouldReceive('isOk')
            ->andReturn(true);
        $cqrsResponse->shouldReceive('getResult')
            ->andReturn(['canResetPassword' => false]);

        $this->expectException(UserCannotResetPasswordException::class);

        $this->sut->forgotPassword('bob');
    }

    public function testForgotPasswordWhenOpenAmServiceeFails()
    {
        /** @var Response|m\Mock $cqrsResponse */
        $cqrsResponse = m::mock(Response::class);

        $this->querySender->shouldReceive('send')
            ->with(\Hamcrest\Matchers::equalTo(Pid::create(['id' => 'bob'])))
            ->andReturn($cqrsResponse);

        $cqrsResponse->shouldReceive('isOk')
            ->andReturn(true);
        $cqrsResponse->shouldReceive('getResult')
            ->andReturn(['canResetPassword' => true]);

        $data = [
            'username' => 'bob',
            'subject' => 'auth.forgot-password.email.subject-translated',
            'message' => 'auth.forgot-password.email.message-translated'
        ];

        $response = new Response();
        $response->setStatusCode(500);
        $response->setContent('{"message": "Test Error Message"}');

        $this->client->shouldReceive('post')
            ->with('json/users/?_action=forgotPassword', $data, null)
            ->andReturn($response);

        $this->expectException(OpenAmResetPasswordFailedException::class);

        try {
            $this->sut->forgotPassword('bob');
        } catch (OpenAmResetPasswordFailedException $exception) {
            $this->assertSame('Test Error Message', $exception->getOpenAmErrorMessage());
            throw $exception;
        }
    }

    public function testForgotPassword()
    {
        /** @var Response|m\Mock $cqrsResponse */
        $cqrsResponse = m::mock(Response::class);

        $this->querySender->shouldReceive('send')
            ->with(\Hamcrest\Matchers::equalTo(Pid::create(['id' => 'bob'])))
            ->andReturn($cqrsResponse);

        $cqrsResponse->shouldReceive('isOk')
            ->andReturn(true);
        $cqrsResponse->shouldReceive('getResult')
            ->andReturn(['canResetPassword' => true]);

        $data = [
            'username' => 'bob',
            'subject' => 'auth.forgot-password.email.subject-translated',
            'message' => 'auth.forgot-password.email.message-translated'
        ];

        $response = new Response();
        $response->setStatusCode(200);
        $response->setContent('{}');

        $this->client->shouldReceive('post')
            ->with('json/users/?_action=forgotPassword', $data, null)
            ->andReturn($response);

        $this->sut->forgotPassword('bob');
    }

    private function getValidMethodName($className, $methodName)
    {
        return (new ReflectionMethod($className, $methodName))->getName();
    }
}
