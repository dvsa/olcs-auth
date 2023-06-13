<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Auth\Controller;

use Common\Service\Cqrs\Command\CommandSender;
use Common\Service\Cqrs\Response;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Dvsa\Olcs\Auth\Container\AuthChallengeContainer;
use Dvsa\Olcs\Auth\Controller\ExpiredPasswordController;
use Dvsa\Olcs\Auth\Form\ChangePasswordForm;
use Dvsa\Olcs\Auth\Service\Auth\ExpiredPasswordService;
use Dvsa\Olcs\Auth\Service\Auth\LoginService;
use Dvsa\Olcs\Transfer\Result\Auth\ChangeExpiredPasswordResult;
use Laminas\Authentication\Storage\Session;
use Laminas\Form\ElementInterface;
use Laminas\Form\Form;
use Laminas\InputFilter\InputFilterInterface;
use Laminas\Mvc\Controller\Plugin\Redirect;
use Laminas\Mvc\Controller\Plugin\Url;
use Laminas\Mvc\Controller\PluginManager;
use Laminas\ServiceManager\ServiceManager;
use Laminas\Stdlib\Parameters;
use Laminas\View\Model\ViewModel;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use RuntimeException;

/**
 * @see ExpiredPasswordController
 */
class ExpiredPasswordControllerTest extends MockeryTestCase
{
    /**
     * @var ExpiredPasswordController
     */
    private $sut;
    /**
     * @var ExpiredPasswordService|m\LegacyMockInterface|m\MockInterface
     */
    private $expiredPasswordService;
    /**
     * @var mixed|m\LegacyMockInterface|m\MockInterface
     */
    private $formHelper;

    /**
     * @var LoginService|m\LegacyMockInterface|m\MockInterface
     */
    private $loginService;

    /**
     * @var m\Mock|CommandSender
     */
    private $commandSender;

    /**
     * @var FlashMessengerHelperService|m\LegacyMockInterface|m\MockInterface
     */
    private $flashMessenger;

    /**
     * @var m\Mock
     */
    private $redirect;

    /**
     * @var AuthChallengeContainer|m\LegacyMockInterface|m\MockInterface
     */
    private $authChallengeContainer;

    /**
     * @var Session|m\LegacyMockInterface|m\MockInterface
     */
    private $sessionContainer;

    /**
     * @var m\Mock
     */
    private $url;


    public function setUp(): void
    {
        $this->formHelper = m::mock(FormHelperService::class);
        $this->loginService = m::mock(LoginService::class);
        $this->expiredPasswordService = m::mock(ExpiredPasswordService::class);
        $this->commandSender = m::mock(CommandSender::class)->makePartial();
        $this->flashMessenger = m::mock(FlashMessengerHelperService::class);
        $this->redirect = m::mock(Redirect::class)->makePartial();
        $this->url = m::mock(Url::class)->makePartial();
        $this->authChallengeContainer = m::mock(AuthChallengeContainer::class);
        $this->sessionContainer = m::mock(Session::class);

        $pm = m::mock(PluginManager::class)->makePartial();
        $pm->setService('redirect', $this->redirect);

        $this->sut = new ExpiredPasswordController(
            $this->authChallengeContainer,
            $this->commandSender,
            $this->expiredPasswordService,
            $this->flashMessenger,
            $this->formHelper,
            $this->loginService,
            $this->sessionContainer,
            false
        );
        $this->sut->setPluginManager($pm);
    }

    public function testIndexActionForGet()
    {
        $form = m::mock(Form::class);
        $form->shouldReceive('remove')->once()->with('oldPassword');

        $inputFilter = m::mock(InputFilterInterface::class);
        $inputFilter->shouldReceive('remove')->once()->with('oldPassword');
        $form->shouldReceive('getInputFilter')->once()->andReturn($inputFilter);

        $this->formHelper->shouldReceive('createForm')
            ->with(ChangePasswordForm::class)
            ->andReturn($form);

        $request = $this->sut->getRequest();
        $request->setMethod('GET');

        $result = $this->sut->indexAction();

        $this->assertInstanceOf(ViewModel::class, $result);
        $this->assertEquals('auth/expired-password', $result->getTemplate());
    }

    public function testIndexActionForPostWithInvalidData()
    {
        $post = [];

        $form = m::mock(Form::class);
        $form->shouldReceive('setData')->once();
        $form->shouldReceive('isValid')->once()->andReturn(false);
        $form->shouldReceive('remove')->once()->with('oldPassword');

        $inputFilter = m::mock(InputFilterInterface::class);
        $inputFilter->shouldReceive('remove')->once()->with('oldPassword');
        $form->shouldReceive('getInputFilter')->once()->andReturn($inputFilter);

        $this->formHelper->shouldReceive('createForm')
            ->once()
            ->with(ChangePasswordForm::class)
            ->andReturn($form);

        $request = $this->sut->getRequest();
        $request->setMethod('POST');
        $request->setPost(new Parameters($post));

        $result = $this->sut->indexAction();

        $this->assertInstanceOf(ViewModel::class, $result);
        $this->assertEquals('auth/expired-password', $result->getTemplate());
    }

    public function testIndexActionForPostWithValidDataSuccess()
    {
        $post = [
            'newPassword' => 'new-password',
            'confirmPassword' => 'confirm-password'
        ];

        $form = m::mock(Form::class);
        $form->shouldReceive('setData')->once();
        $form->shouldReceive('isValid')->once()->andReturn(true);
        $form->shouldReceive('getData')->once()->andReturn($post);
        $form->shouldReceive('remove')->once()->with('oldPassword');

        $inputFilter = m::mock(InputFilterInterface::class);
        $inputFilter->shouldReceive('remove')->once()->with('oldPassword');
        $form->shouldReceive('getInputFilter')->once()->andReturn($inputFilter);

        $this->formHelper->shouldReceive('createForm')
            ->with(ChangePasswordForm::class)
            ->andReturn($form);

        $this->authChallengeContainer
            ->shouldReceive('getChallengeName')
            ->once()
            ->andReturn(AuthChallengeContainer::CHALLENEGE_NEW_PASWORD_REQUIRED);
        $this->authChallengeContainer
            ->shouldReceive('getChallengeSession')
            ->once()
            ->andReturn('challenge-session');
        $this->authChallengeContainer
            ->shouldReceive('getChallengedIdentity')
            ->once()
            ->andReturn('identity');
        $this->authChallengeContainer
            ->shouldReceive('clear')
            ->once();

        $mockResponse = m::mock(Response::class);
        $mockResponse->shouldReceive('isOk')
            ->once()
            ->andReturnTrue();
        $mockResponse->shouldReceive('getResult')
            ->once()
            ->andReturn(['flags' => ['code' => 1, 'identity' => ['username' => 'example']]]);

        $this->commandSender->shouldReceive('send')
            ->once()
            ->andReturn($mockResponse);

        $this->sessionContainer
            ->shouldReceive('write')
            ->once()
            ->with(['username' => 'example']);

        $this->redirect
            ->shouldReceive('toRoute')
            ->once()
            ->with(ExpiredPasswordController::ROUTE_INDEX)
            ->andReturn('REDIRECT');

        $request = $this->sut->getRequest();
        $request->setMethod('POST');
        $request->setPost(new Parameters($post));

        $this->assertEquals('REDIRECT', $this->sut->indexAction());
    }

    public function testIndexActionForPostWithValidDataWrongChallenge()
    {
        $post = [
            'newPassword' => 'new-password',
            'confirmPassword' => 'confirm-password'
        ];

        $form = m::mock(Form::class);
        $form->shouldReceive('setData')->once();
        $form->shouldReceive('isValid')->once()->andReturn(true);
        $form->shouldReceive('getData')->once()->andReturn($post);
        $form->shouldReceive('remove')->once()->with('oldPassword');

        $inputFilter = m::mock(InputFilterInterface::class);
        $inputFilter->shouldReceive('remove')->once()->with('oldPassword');
        $form->shouldReceive('getInputFilter')->once()->andReturn($inputFilter);

        $this->formHelper->shouldReceive('createForm')
            ->with(ChangePasswordForm::class)
            ->andReturn($form);

        $this->authChallengeContainer
            ->shouldReceive('getChallengeName')
            ->once()
            ->andReturn('challenge-name');

        $request = $this->sut->getRequest();
        $request->setMethod('POST');
        $request->setPost(new Parameters($post));

        $this->expectException(RuntimeException::class);

        $this->sut->indexAction();
    }

    public function testIndexActionForPostWithValidDataResultNotOk()
    {
        $post = [
            'newPassword' => 'new-password',
            'confirmPassword' => 'confirm-password'
        ];

        $form = m::mock(Form::class);
        $form->shouldReceive('setData')->once();
        $form->shouldReceive('isValid')->once()->andReturn(true);
        $form->shouldReceive('getData')->once()->andReturn($post);
        $form->shouldReceive('remove')->once()->with('oldPassword');

        $inputFilter = m::mock(InputFilterInterface::class);
        $inputFilter->shouldReceive('remove')->once()->with('oldPassword');
        $form->shouldReceive('getInputFilter')->once()->andReturn($inputFilter);

        $this->formHelper->shouldReceive('createForm')
            ->with(ChangePasswordForm::class)
            ->andReturn($form);

        $this->authChallengeContainer
            ->shouldReceive('getChallengeName')
            ->once()
            ->andReturn(AuthChallengeContainer::CHALLENEGE_NEW_PASWORD_REQUIRED);
        $this->authChallengeContainer
            ->shouldReceive('getChallengeSession')
            ->once()
            ->andReturn('challenge-session');
        $this->authChallengeContainer
            ->shouldReceive('getChallengedIdentity')
            ->once()
            ->andReturn('identity');

        $mockResponse = m::mock(Response::class);
        $mockResponse->shouldReceive('isOk')
            ->once()
            ->andReturnFalse();

        $this->commandSender->shouldReceive('send')
            ->once()
            ->andReturn($mockResponse);

        $request = $this->sut->getRequest();
        $request->setMethod('POST');
        $request->setPost(new Parameters($post));

        $this->expectException(RuntimeException::class);

        $this->sut->indexAction();
    }

    public function testIndexActionForPostWithValidDataNewPasswordInvalid()
    {
        $post = [
            'newPassword' => 'new-password',
            'confirmPassword' => 'confirm-password'
        ];

        $form = m::mock(Form::class);
        $form->shouldReceive('setData')->once();
        $form->shouldReceive('isValid')->once()->andReturn(true);
        $form->shouldReceive('getData')->once()->andReturn($post);
        $form->shouldReceive('remove')->once()->with('oldPassword');

        $inputFilter = m::mock(InputFilterInterface::class);
        $inputFilter->shouldReceive('remove')->once()->with('oldPassword');
        $form->shouldReceive('getInputFilter')->once()->andReturn($inputFilter);

        $this->formHelper->shouldReceive('createForm')
            ->once()
            ->with(ChangePasswordForm::class)
            ->andReturn($form);

        $this->authChallengeContainer
            ->shouldReceive('getChallengeName')
            ->once()
            ->andReturn(AuthChallengeContainer::CHALLENEGE_NEW_PASWORD_REQUIRED);
        $this->authChallengeContainer
            ->shouldReceive('getChallengeSession')
            ->once()
            ->andReturn('challenge-session');
        $this->authChallengeContainer
            ->shouldReceive('getChallengedIdentity')
            ->once()
            ->andReturn('identity');

        $mockResponse = m::mock(Response::class);
        $mockResponse->shouldReceive('isOk')
            ->once()
            ->andReturnTrue();

        $mockResponse->shouldReceive('getResult')
            ->once()
            ->andReturn([
                'flags' => [
                    'code' => ChangeExpiredPasswordResult::FAILURE_NEW_PASSWORD_INVALID
                ]
            ]);

        $this->commandSender->shouldReceive('send')
            ->once()
            ->andReturn($mockResponse);

        $element = m::mock(ElementInterface::class);
        $element->shouldReceive('setMessages')
            ->once();
        $form->shouldReceive('get')
            ->once()
            ->with('newPassword')
            ->andReturn($element);

        $request = $this->sut->getRequest();
        $request->setMethod('POST');
        $request->setPost(new Parameters($post));

        $result = $this->sut->indexAction();

        $this->assertInstanceOf(ViewModel::class, $result);
        $this->assertEquals('auth/expired-password', $result->getTemplate());
    }
    public function testIndexActionForPostWithValidDataNewPasswordMatchesOld()
    {
        $post = [
            'newPassword' => 'new-password',
            'confirmPassword' => 'confirm-password'
        ];

        $form = m::mock(Form::class);
        $form->shouldReceive('setData')->once();
        $form->shouldReceive('isValid')->once()->andReturn(true);
        $form->shouldReceive('getData')->once()->andReturn($post);
        $form->shouldReceive('remove')->once()->with('oldPassword');

        $inputFilter = m::mock(InputFilterInterface::class);
        $inputFilter->shouldReceive('remove')->once()->with('oldPassword');
        $form->shouldReceive('getInputFilter')->once()->andReturn($inputFilter);

        $this->formHelper->shouldReceive('createForm')
            ->once()
            ->with(ChangePasswordForm::class)
            ->andReturn($form);

        $this->authChallengeContainer
            ->shouldReceive('getChallengeName')
            ->once()
            ->andReturn(AuthChallengeContainer::CHALLENEGE_NEW_PASWORD_REQUIRED);
        $this->authChallengeContainer
            ->shouldReceive('getChallengeSession')
            ->once()
            ->andReturn('challenge-session');
        $this->authChallengeContainer
            ->shouldReceive('getChallengedIdentity')
            ->once()
            ->andReturn('identity');

        $mockResponse = m::mock(Response::class);
        $mockResponse->shouldReceive('isOk')
            ->once()
            ->andReturnTrue();

        $mockResponse->shouldReceive('getResult')
            ->once()
            ->andReturn([
                'flags' => [
                    'code' => ChangeExpiredPasswordResult::FAILURE_NEW_PASSWORD_MATCHES_OLD
                ]
            ]);

        $this->commandSender->shouldReceive('send')
            ->once()
            ->andReturn($mockResponse);

        $element = m::mock(ElementInterface::class);
        $element->shouldReceive('setOption')
            ->once()
            ->with('error-message', null);
        $element->shouldReceive('setMessages')
            ->once();
        $form->shouldReceive('get')
            ->once()
            ->with('newPassword')
            ->andReturn($element);

        $request = $this->sut->getRequest();
        $request->setMethod('POST');
        $request->setPost(new Parameters($post));

        $result = $this->sut->indexAction();

        $this->assertInstanceOf(ViewModel::class, $result);
        $this->assertEquals('auth/expired-password', $result->getTemplate());
    }

    public function testIndexActionForPostWithValidDataNotAuthorizedFailure()
    {
        $post = [
            'newPassword' => 'new-password',
            'confirmPassword' => 'confirm-password'
        ];

        $form = m::mock(Form::class);
        $form->shouldReceive('setData')->once();
        $form->shouldReceive('isValid')->once()->andReturn(true);
        $form->shouldReceive('getData')->once()->andReturn($post);
        $form->shouldReceive('remove')->once()->with('oldPassword');

        $inputFilter = m::mock(InputFilterInterface::class);
        $inputFilter->shouldReceive('remove')->once()->with('oldPassword');
        $form->shouldReceive('getInputFilter')->once()->andReturn($inputFilter);

        $this->formHelper->shouldReceive('createForm')
            ->once()
            ->with(ChangePasswordForm::class)
            ->andReturn($form);

        $this->authChallengeContainer
            ->shouldReceive('getChallengeName')
            ->once()
            ->andReturn(AuthChallengeContainer::CHALLENEGE_NEW_PASWORD_REQUIRED);
        $this->authChallengeContainer
            ->shouldReceive('getChallengeSession')
            ->once()
            ->andReturn('challenge-session');
        $this->authChallengeContainer
            ->shouldReceive('getChallengedIdentity')
            ->once()
            ->andReturn('identity');

        $this->flashMessenger
            ->shouldReceive('addErrorMessage')
            ->once()
            ->with('message1');
        $this->flashMessenger
            ->shouldReceive('addErrorMessage')
            ->once()
            ->with('message2');

        $mockResponse = m::mock(Response::class);
        $mockResponse->shouldReceive('isOk')
            ->once()
            ->andReturnTrue();
        $mockResponse->shouldReceive('getResult')
            ->once()
            ->andReturn([
                'flags' => [
                    'code' => ChangeExpiredPasswordResult::FAILURE_NOT_AUTHORIZED,
                    'messages' => [
                        'message1',
                        'message2'
                    ]
                ]
            ]);

        $this->commandSender->shouldReceive('send')
            ->once()
            ->andReturn($mockResponse);

        $this->redirect
            ->shouldReceive('toRoute')
            ->once()
            ->with(ExpiredPasswordController::ROUTE_LOGIN)
            ->andReturn('REDIRECT');

        $request = $this->sut->getRequest();
        $request->setMethod('POST');
        $request->setPost(new Parameters($post));

        $this->assertEquals('REDIRECT', $this->sut->indexAction());
    }

    public function testIndexActionForPostWithValidDataInvalidResponse()
    {
        $post = [
            'newPassword' => 'new-password',
            'confirmPassword' => 'confirm-password'
        ];

        $form = m::mock(Form::class);
        $form->shouldReceive('setData')->once();
        $form->shouldReceive('isValid')->once()->andReturn(true);
        $form->shouldReceive('getData')->once()->andReturn($post);
        $form->shouldReceive('remove')->once()->with('oldPassword');

        $inputFilter = m::mock(InputFilterInterface::class);
        $inputFilter->shouldReceive('remove')->once()->with('oldPassword');
        $form->shouldReceive('getInputFilter')->once()->andReturn($inputFilter);

        $this->formHelper->shouldReceive('createForm')
            ->with(ChangePasswordForm::class)
            ->andReturn($form);

        $this->authChallengeContainer
            ->shouldReceive('getChallengeName')
            ->once()
            ->andReturn(AuthChallengeContainer::CHALLENEGE_NEW_PASWORD_REQUIRED);
        $this->authChallengeContainer
            ->shouldReceive('getChallengeSession')
            ->once()
            ->andReturn('challenge-session');
        $this->authChallengeContainer
            ->shouldReceive('getChallengedIdentity')
            ->once()
            ->andReturn('identity');

        $mockResponse = m::mock(Response::class);
        $mockResponse->shouldReceive('isOk')
            ->once()
            ->andReturnTrue();
        $mockResponse->shouldReceive('getResult')
            ->once()
            ->andReturn([
                'flags' => [
                    'code' => ChangeExpiredPasswordResult::FAILURE_CLIENT_ERROR,
                    'messages' => [
                        'message1',
                        'message2'
                    ]
                ]
            ]);

        $this->commandSender->shouldReceive('send')
            ->andReturn($mockResponse);

        $request = $this->sut->getRequest();
        $request->setMethod('POST');
        $request->setPost(new Parameters($post));

        $this->expectException(RuntimeException::class);

        $this->sut->indexAction();
    }
}
