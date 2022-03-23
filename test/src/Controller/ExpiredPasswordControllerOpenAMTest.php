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
use Laminas\Form\Form;
use Laminas\Http\Request as HttpRequest;
use Laminas\Mvc\Controller\Plugin\Params;
use Laminas\Mvc\Controller\Plugin\Redirect;
use Laminas\Mvc\Controller\Plugin\Url;
use Laminas\Mvc\Controller\PluginManager;
use Laminas\ServiceManager\ServiceManager;
use Laminas\Stdlib\Parameters;
use Laminas\View\Model\ViewModel;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * @see ExpiredPasswordController
 */
class ExpiredPasswordControllerOpenAMTest extends MockeryTestCase
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

    /**
     * @var m\Mock
     */
    private $params;


    public function setUp(): void
    {
        $this->formHelper = m::mock(FormHelperService::class);
        $this->loginService = m::mock(LoginService::class);
        $this->expiredPasswordService = m::mock(ExpiredPasswordService::class);
        $this->commandSender = m::mock(CommandSender::class)->makePartial();
        $this->flashMessenger = m::mock(FlashMessengerHelperService::class);
        $this->redirect = m::mock(Redirect::class)->makePartial();
        $this->params = m::mock(Params::class)->makePartial();
        $this->url = m::mock(Url::class)->makePartial();
        $this->authChallengeContainer = m::mock(AuthChallengeContainer::class);
        $this->sessionContainer = m::mock(Session::class);

        $sm = m::mock(ServiceManager::class)->makePartial();

        $pm = m::mock(PluginManager::class)->makePartial();
        $pm->setService('redirect', $this->redirect);
        $pm->setService('params', $this->params);

        $this->sut = new ExpiredPasswordController(
            $this->authChallengeContainer,
            $this->commandSender,
            $this->expiredPasswordService,
            $this->flashMessenger,
            $this->formHelper,
            $this->loginService,
            $this->sessionContainer,
            true
        );
        $this->sut->setServiceLocator($sm);
        $this->sut->setPluginManager($pm);
    }

    public function testIndexActionForPostWithValidDataSuccess()
    {
        $post = [
            'oldPassword' => 'old-password',
            'newPassword' => 'new-password',
            'confirmPassword' => 'confirm-password'
        ];

        $form = m::mock(Form::class);
        $form->shouldReceive('setData')->once();
        $form->shouldReceive('isValid')->once()->andReturn(true);
        $form->shouldReceive('getData')->once()->andReturn($post);

        $this->formHelper->shouldReceive('createForm')
            ->with(ChangePasswordForm::class)
            ->andReturn($form);

        $this->params
            ->shouldReceive('__invoke')
            ->once()
            ->with('authId')
            ->andReturn('auth-id');

        $this->expiredPasswordService
            ->shouldReceive('updatePassword')
            ->once()
            ->with('auth-id', 'old-password', 'new-password', 'confirm-password')
            ->andReturn(['tokenId' => 'token-id', 'status' => 200]);

        $this->redirect
            ->shouldReceive('toUrl')
            ->once()
            ->with('/')
            ->andReturn('REDIRECT');

        $this->loginService
            ->shouldReceive('login')
            ->once()
            ->withSomeOfArgs('token-id')
            ->andReturn('/');

        $request = $this->sut->getRequest();
        $request->setMethod('POST');
        $request->setPost(new Parameters($post));

        $this->assertEquals('REDIRECT', $this->sut->indexAction());
    }

    public function testIndexActionForPostWithValidDataStatusNot200()
    {
        $post = [
            'oldPassword' => 'old-password',
            'newPassword' => 'new-password',
            'confirmPassword' => 'confirm-password'
        ];

        $form = m::mock(Form::class);
        $form->shouldReceive('setData')->once();
        $form->shouldReceive('isValid')->once()->andReturn(true);
        $form->shouldReceive('getData')->once()->andReturn($post);

        $this->formHelper->shouldReceive('createForm')
            ->with(ChangePasswordForm::class)
            ->andReturn($form);

        $this->params
            ->shouldReceive('__invoke')
            ->once()
            ->with('authId')
            ->andReturn('auth-id');

        $this->expiredPasswordService
            ->shouldReceive('updatePassword')
            ->once()
            ->with('auth-id', 'old-password', 'new-password', 'confirm-password')
            ->andReturn(['status' => 500]);

        $this->flashMessenger
            ->shouldReceive('addUnknownError')
            ->once();

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

    public function testIndexActionForPostWithValidDataStatusMissingTokenId()
    {
        $post = [
            'oldPassword' => 'old-password',
            'newPassword' => 'new-password',
            'confirmPassword' => 'confirm-password'
        ];

        $form = m::mock(Form::class);
        $form->shouldReceive('setData')->once();
        $form->shouldReceive('isValid')->once()->andReturn(true);
        $form->shouldReceive('getData')->once()->andReturn($post);

        $this->formHelper->shouldReceive('createForm')
            ->with(ChangePasswordForm::class)
            ->andReturn($form);

        $this->params
            ->shouldReceive('__invoke')
            ->once()
            ->with('authId')
            ->andReturn('auth-id');

        $this->expiredPasswordService
            ->shouldReceive('updatePassword')
            ->once()
            ->with('auth-id', 'old-password', 'new-password', 'confirm-password')
            ->andReturn(['status' => 200, 'header' => 'header']);


        $request = $this->sut->getRequest();
        $request->setMethod('POST');
        $request->setPost(new Parameters($post));

        $result =  $this->sut->indexAction();
        $this->assertInstanceOf(ViewModel::class, $result);
        $this->assertEquals('auth/expired-password', $result->getTemplate());
    }
}
