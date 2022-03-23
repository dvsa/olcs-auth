<?php

/**
 * Change Password Controller Test
 */
namespace Dvsa\OlcsTest\Auth\Controller;

use Common\Rbac\JWTIdentityProvider;
use Common\Rbac\PidIdentityProvider;
use Common\Service\Cqrs\Command\CommandSender;
use Common\Service\Cqrs\Response;
use Dvsa\Olcs\Auth\Controller\ChangePasswordController;
use Dvsa\Olcs\Auth\Form\ChangePasswordForm;
use Dvsa\Olcs\Transfer\Result\Auth\ChangePasswordResult;
use Laminas\Http\Response as HttpResponse;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Laminas\Form\Form;
use Laminas\Http\Request as HttpRequest;
use Laminas\Mvc\Controller\Plugin\Redirect;
use Laminas\Mvc\Controller\PluginManager;
use Laminas\ServiceManager\ServiceManager;
use Laminas\View\Model\ViewModel;
use RuntimeException;

/**
 * Change Password Controller Test
 */
class ChangePasswordControllerTest extends MockeryTestCase
{
    /**
     * @var ChangePasswordController
     */
    private $sut;

    private $formHelper;

    private $changePasswordService;

    private $flashMessenger;

    private $redirect;

    /**
     * @var CommandSender|m\LegacyMockInterface|m\MockInterface
     */
    private $commandSender;

    private array $config;

    public function setUp(): void
    {
        $this->formHelper = m::mock();
        $this->changePasswordService = m::mock();
        $this->flashMessenger = m::mock();
        $this->redirect = m::mock(Redirect::class)->makePartial();
        $this->commandSender = m::mock(CommandSender::class);

        $sm = m::mock(ServiceManager::class)->makePartial();
        $sm->setService('Helper\Form', $this->formHelper);
        $sm->setService('Auth\ChangePasswordService', $this->changePasswordService);
        $sm->setService('Helper\FlashMessenger', $this->flashMessenger);
        $sm->setService('CommandSender', $this->commandSender);

        $this->config = [
            'my_account_route' => 'my-account',
            'auth' => [
                'identity_provider' => JWTIdentityProvider::class
            ]
        ];
        $sm->setService('Config', $this->config);

        $pm = m::mock(PluginManager::class)->makePartial();
        $pm->setService('redirect', $this->redirect);

        $this->sut = new ChangePasswordController();
        $this->sut->setServiceLocator($sm);
        $this->sut->setPluginManager($pm);
    }

    public function testIndexActionForGet()
    {
        $form = m::mock(Form::class);

        $this->formHelper->shouldReceive('createFormWithRequest')
            ->with(ChangePasswordForm::class, m::type(HttpRequest::class))
            ->andReturn($form);

        $request = $this->sut->getRequest();
        $request->setMethod('GET');

        $result = $this->sut->indexAction();

        $this->assertInstanceOf(ViewModel::class, $result);
        $this->assertEquals('auth/change-password', $result->getTemplate());
    }

    public function testIndexActionForPostWithInvalidData()
    {
        $post = [];

        $form = m::mock(Form::class);
        $form->shouldReceive('setData')->once();
        $form->shouldReceive('isValid')->once()->andReturn(false);

        $this->formHelper->shouldReceive('createFormWithRequest')
            ->with(ChangePasswordForm::class, m::type(HttpRequest::class))
            ->andReturn($form);

        $request = $this->sut->getRequest();
        $request->setMethod('POST');
        $request->setPost(new \Laminas\Stdlib\Parameters($post));

        $result = $this->sut->indexAction();

        $this->assertInstanceOf(ViewModel::class, $result);
        $this->assertEquals('auth/change-password', $result->getTemplate());
    }

    public function testIndexActionForPostWithCancel()
    {
        $post = [
            'cancel' => '',
        ];

        $form = m::mock(Form::class);

        $this->formHelper->shouldReceive('createFormWithRequest')
            ->with(ChangePasswordForm::class, m::type(HttpRequest::class))
            ->andReturn($form);

        $request = $this->sut->getRequest();
        $request->setMethod('POST');
        $request->setPost(new \Laminas\Stdlib\Parameters($post));

        $this->redirect->shouldReceive('toRouteAjax')->with('my-account')->andReturn('REDIRECT');

        $this->assertEquals('REDIRECT', $this->sut->indexAction());
    }

    public function testIndexActionForPostWithValidDataSuccess()
    {
        $post = [
            'oldPassword' => 'old-password',
            'newPassword' => 'new-password',
        ];

        $form = m::mock(Form::class);
        $form->shouldReceive('setData')->once();
        $form->shouldReceive('isValid')->once()->andReturn(true);
        $form->shouldReceive('getData')->once()->andReturn($post);

        $this->formHelper->shouldReceive('createFormWithRequest')
            ->with(ChangePasswordForm::class, m::type(HttpRequest::class))
            ->andReturn($form);

        $request = $this->sut->getRequest();
        $request->setMethod('POST');
        $request->setPost(new \Laminas\Stdlib\Parameters($post));

        $response = new HttpResponse();
        $response->setStatusCode(HttpResponse::STATUS_CODE_200);
        $result = new Response($response);
        $result->setResult([
            'flags' => [
                'code' => ChangePasswordResult::SUCCESS
            ]
        ]);

        $this->commandSender->shouldReceive('send')->andReturn($result);

        $this->flashMessenger->shouldReceive('addSuccessMessage')
            ->with('auth.change-password.success')
            ->once();

        $this->redirect->shouldReceive('toRouteAjax')->with('my-account')->andReturn('REDIRECT');

        $this->assertEquals('REDIRECT', $this->sut->indexAction());
    }

    public function testIndexActionForPostWithValidDataFailure()
    {
        $post = [
            'oldPassword' => 'old-password',
            'newPassword' => 'new-password',
        ];

        $form = m::mock(Form::class);
        $form->shouldReceive('setData')->once();
        $form->shouldReceive('isValid')->once()->andReturn(true);
        $form->shouldReceive('getData')->once()->andReturn($post);

        $this->formHelper->shouldReceive('createFormWithRequest')
            ->with(ChangePasswordForm::class, m::type(HttpRequest::class))
            ->andReturn($form);

        $request = $this->sut->getRequest();
        $request->setMethod('POST');
        $request->setPost(new \Laminas\Stdlib\Parameters($post));

        $response = new HttpResponse();
        $response->setStatusCode(HttpResponse::STATUS_CODE_200);
        $result = new Response($response);
        $result->setResult([
            'flags' => [
                'code' => ChangePasswordResult::FAILURE_OLD_PASSWORD_INVALID,
                'message' => 'error message'
            ]
        ]);

        $this->commandSender->shouldReceive('send')->andReturn($result);

        $result = $this->sut->indexAction();

        $this->assertInstanceOf(ViewModel::class, $result);
        $this->assertEquals('auth/change-password', $result->getTemplate());
        $this->assertEquals(true, $result->getVariable('failed'));
        $this->assertEquals('error message', $result->getVariable('failureReason'));
    }

    public function testIndexActionForPostWithValidDataBadResult()
    {
        $post = [
            'oldPassword' => 'old-password',
            'newPassword' => 'new-password',
        ];

        $form = m::mock(Form::class);
        $form->shouldReceive('setData')->once();
        $form->shouldReceive('isValid')->once()->andReturn(true);
        $form->shouldReceive('getData')->once()->andReturn($post);

        $this->formHelper->shouldReceive('createFormWithRequest')
            ->with(ChangePasswordForm::class, m::type(HttpRequest::class))
            ->andReturn($form);

        $request = $this->sut->getRequest();
        $request->setMethod('POST');
        $request->setPost(new \Laminas\Stdlib\Parameters($post));

        $response = new HttpResponse();
        $response->setStatusCode(HttpResponse::STATUS_CODE_500);
        $result = new Response($response);

        $this->commandSender->shouldReceive('send')->andReturn($result);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(sprintf(ChangePasswordController::MESSAGE_BASE, ChangePasswordController::MESSAGE_RESULT_NOT_OK));

        $this->sut->indexAction();
    }
}
