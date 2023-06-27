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
            $this->sessionContainer
        );
        $this->sut->setPluginManager($pm);
    }

    public function testIndexActionForGet()
    {
        $form = m::mock(Form::class);
        $form->expects('remove')
            ->with('oldPassword');

        $inputFilter = m::mock(InputFilterInterface::class);
        $inputFilter->expects('remove')
            ->with('oldPassword');

        $form->expects('getInputFilter')
            ->andReturn($inputFilter);

        $this->formHelper->shouldReceive('createForm')
            ->with(ChangePasswordForm::class)
            ->andReturn($form);

        $request = $this->sut->getRequest();
        $request->setMethod('GET');

        $result = $this->sut->indexAction();

        $this->assertInstanceOf(ViewModel::class, $result);
        $this->assertEquals('auth/expired-password', $result->getTemplate());
    }

    public function testIndexActionForInvalidPostRequest()
    {
        $post = [];

        $form = m::mock(Form::class);

        $form->expects('remove')->with('oldPassword');

        $inputFilter = m::mock(InputFilterInterface::class);
        $inputFilter->expects('remove')->with('oldPassword');

        $form->expects('getInputFilter')->andReturn($inputFilter);

        $this->formHelper->expects('createForm')
            ->with(ChangePasswordForm::class)
            ->andReturn($form);

        $request = $this->sut->getRequest();
        $request->setMethod('GET');

        $result = $this->sut->indexAction();

        $this->assertInstanceOf(ViewModel::class, $result);
        $this->assertEquals('auth/expired-password', $result->getTemplate());
    }
    public function testIndexActionForPostWithInvalidDataOnForm()
    {
        $post = [];

        $form = m::mock(Form::class);
        $form->expects('setData');
        $form->expects('isValid')->andReturn(false);

        $form->expects('remove')->with('oldPassword');

        $inputFilter = m::mock(InputFilterInterface::class);
        $inputFilter->expects('remove')->with('oldPassword');

        $form->expects('getInputFilter')->andReturn($inputFilter);

        $this->formHelper->expects('createForm')
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
        $post = $this->setUpPostData();
        $form = $this->setUpInitialForm($post);

        $this->authChallengeContainer
            ->expects('getChallengeName')
            ->andReturn(AuthChallengeContainer::CHALLENEGE_NEW_PASWORD_REQUIRED);
        $this->authChallengeContainer
            ->expects('getChallengeSession')
            ->andReturn('challenge-session');
        $this->authChallengeContainer
            ->expects('getChallengedIdentity')
            ->andReturn('identity');
        $this->authChallengeContainer
            ->expects('clear');

        $mockResponse = m::mock(Response::class);
        $mockResponse->expects('isOk')
            ->andReturnTrue();
        $mockResponse->expects('getResult')
            ->andReturn(['flags' =>
                ['code' => 1,
                    'identity' =>
                        ['username' => 'example']]]);

        $this->commandSender->expects('send')
            ->andReturn($mockResponse);

        $this->sessionContainer
            ->expects('write')
            ->with(['username' => 'example']);

        $this->redirect
            ->expects('toRoute')
            ->with(ExpiredPasswordController::ROUTE_INDEX)
            ->andReturn('REDIRECT');

        $request = $this->sut->getRequest();
        $request->setMethod('POST');
        $request->setPost(new Parameters($post));

        $this->assertEquals('REDIRECT', $this->sut->indexAction());
    }

    public function testIndexActionForPostWithValidDataWrongChallenge()
    {
        $post = $this->setUpPostData();
        $form = $this->setUpInitialForm($post);
        $this->authChallengeContainer
            ->expects('getChallengeName')
            ->andReturn('challenge-name');

        $request = $this->sut->getRequest();
        $request->setMethod('POST');
        $request->setPost(new Parameters($post));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(ExpiredPasswordController::MESSAGE_CHALLENGE_NOT_NEW_PASSWORD_REQUIRED);


        $this->sut->indexAction();

    }

    public function testIndexActionForPostWithValidDataResultNotOk()
    {
        $post = $this->setUpPostData();
        $form = $this->setUpInitialForm($post);

        $this->authChallengeContainer
            ->expects('getChallengeName')
            ->andReturn(AuthChallengeContainer::CHALLENEGE_NEW_PASWORD_REQUIRED);
        $this->authChallengeContainer
            ->expects('getChallengeSession')
            ->andReturn('challenge-session');
        $this->authChallengeContainer
            ->expects('getChallengedIdentity')
            ->andReturn('identity');

        $mockResponse = m::mock(Response::class);
        $mockResponse->expects('isOk')
            ->andReturnFalse();

        $this->commandSender->expects('send')
            ->andReturn($mockResponse);

        $request = $this->sut->getRequest();
        $request->setMethod('POST');
        $request->setPost(new Parameters($post));
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(ExpiredPasswordController::MESSAGE_RESULT_NOT_OK);

        $this->sut->indexAction();
    }

    /**
     *
     * @dataProvider errorMessagesDataProvider
     */
     public function testIndexActionForPostWithValidDataNewPasswordInvalid($errorMessage)
     {
        $post = $this->setUpPostData();
        $form = $this->setUpInitialForm($post);
        $element = m::mock(ElementInterface::class);

        $element->expects('setMessages');
        $form->expects('get')
            ->with('newPassword')
            ->andReturn($element);
        $this->authChallengeContainer
            ->expects('getChallengeName')
            ->andReturn(AuthChallengeContainer::CHALLENEGE_NEW_PASWORD_REQUIRED);
        $this->authChallengeContainer
            ->expects('getChallengeSession')
            ->andReturn('challenge-session');
        $this->authChallengeContainer
            ->expects('getChallengedIdentity')
            ->andReturn('identity');

        $mockResponse = m::mock(Response::class);
        $mockResponse->expects('isOk')
            ->andReturnTrue();
        $mockResponse->expects('getResult')
            ->andReturn([
                'flags' => [
                    'code' =>  $errorMessage
                ]
            ]);
        $this->commandSender->expects('send')
            ->andReturn($mockResponse);

        $request = $this->sut->getRequest();
        $request->setMethod('POST');
        $request->setPost(new Parameters($post));

        $result = $this->sut->indexAction();

        $this->assertInstanceOf(ViewModel::class, $result);
        $this->assertEquals('auth/expired-password', $result->getTemplate());
    }
    public function setUpPostData()
    {
        $post = [
            'newPassword' => 'new-password',
            'confirmPassword' => 'confirm-password'
        ];
        return $post;
    }
    public function getFormMock($post)
    {
        $form = m::mock(Form::class);
        $form->expects('setData');
        $form->expects('isValid')->andReturn(true);
        $form->expects('getData')->andReturn($post);
        $form->expects('remove')->with('oldPassword');
        return $form;
    }

    public function getInputFilter()
    {
        $inputFilter = m::mock(InputFilterInterface::class);
        $inputFilter->expects('remove')
            ->with('oldPassword');

        return $inputFilter;
    }
    public function setUpInitialForm($post)
    {
        $form = $this->getFormMock($post);
        $inputFilter = $this->getInputFilter();

        $form->expects('getInputFilter')
            ->andReturn($inputFilter);

        $this->formHelper->expects('createForm')
            ->with(ChangePasswordForm::class)
            ->andReturn($form);
        return $form;
    }
    /**
     * @return array[]
     * dataProvider for testIndexActionForPostWithValidDataNewPasswordInvalid
     */
    public function errorMessagesDataProvider()
    {
        return [
            'FAILURE_NEW_PASSWORD_INVALID'=> [ChangeExpiredPasswordResult::FAILURE_NEW_PASSWORD_INVALID],
            'FAILURE_NEW_PASSWORD_MATCHES_OLD' => [ChangeExpiredPasswordResult::FAILURE_NEW_PASSWORD_MATCHES_OLD],
            ];
    }

    public function testIndexActionForPostWithValidDataNotAuthorizedFailure()
    {
        $post = $this->setUpPostData();
        $form = $this->setUpInitialForm($post);

        $this->authChallengeContainer
            ->expects('getChallengeName')
            ->andReturn(AuthChallengeContainer::CHALLENEGE_NEW_PASWORD_REQUIRED);
        $this->authChallengeContainer
            ->expects('getChallengeSession')
            ->andReturn('challenge-session');
        $this->authChallengeContainer
            ->expects('getChallengedIdentity')
            ->andReturn('identity');

        $this->flashMessenger
            ->expects('addErrorMessage')
            ->with('message1');
        $this->flashMessenger
            ->expects('addErrorMessage')
            ->with('message2');

        $mockResponse = m::mock(Response::class);
        $mockResponse->expects('isOk')
            ->andReturnTrue();
        $mockResponse->expects('getResult')
            ->andReturn([
                'flags' => [
                    'code' => ChangeExpiredPasswordResult::FAILURE_NOT_AUTHORIZED,
                    'messages' => [
                        'message1',
                        'message2'
                    ]
                ]
            ]);

        $this->commandSender->expects('send')
            ->andReturn($mockResponse);

        $this->redirect
            ->expects('toRoute')
            ->with(ExpiredPasswordController::ROUTE_LOGIN)
            ->andReturn('REDIRECT');

        $request = $this->sut->getRequest();
        $request->setMethod('POST');
        $request->setPost(new Parameters($post));

        $this->assertEquals('REDIRECT', $this->sut->indexAction());
    }

    public function testIndexActionForPostWithValidDataInvalidResponse()
    {
        $post = $this->setUpPostData();
        $form = $this->setUpInitialForm($post);

        $this->authChallengeContainer
            ->expects('getChallengeName')
            ->andReturn(AuthChallengeContainer::CHALLENEGE_NEW_PASWORD_REQUIRED);
        $this->authChallengeContainer
            ->expects('getChallengeSession')
            ->andReturn('challenge-session');
        $this->authChallengeContainer
            ->expects('getChallengedIdentity')
            ->andReturn('identity');

        $mockResponse = m::mock(Response::class);
        $mockResponse->expects('isOk')
            ->andReturnTrue();
        $mockResponse->expects('getResult')
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
