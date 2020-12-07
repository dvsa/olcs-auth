<?php

/**
 * Change Password Controller Test
 */
namespace Dvsa\OlcsTest\Auth\Controller;

use Dvsa\Olcs\Auth\Controller\ChangePasswordController;
use Dvsa\Olcs\Auth\Form\ChangePasswordForm;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Laminas\Form\Form;
use Laminas\Http\Request as HttpRequest;
use Laminas\Mvc\Controller\Plugin\Redirect;
use Laminas\Mvc\Controller\PluginManager;
use Laminas\ServiceManager\ServiceManager;
use Laminas\View\Model\ViewModel;

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

    public function setUp(): void
    {
        $this->formHelper = m::mock();
        $this->changePasswordService = m::mock();
        $this->flashMessenger = m::mock();
        $this->redirect = m::mock(Redirect::class)->makePartial();

        $sm = m::mock(ServiceManager::class)->makePartial();
        $sm->setService('Helper\Form', $this->formHelper);
        $sm->setService('Auth\ChangePasswordService', $this->changePasswordService);
        $sm->setService('Helper\FlashMessenger', $this->flashMessenger);

        $config = [
            'my_account_route' => 'my-account'
        ];
        $sm->setService('Config', $config);

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

        $this->changePasswordService->shouldReceive('updatePassword')
            ->with($request, $post['oldPassword'], $post['newPassword'])
            ->andReturn(['status' => 200, 'message' => 'error message']);

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

        $this->changePasswordService->shouldReceive('updatePassword')
            ->with($request, $post['oldPassword'], $post['newPassword'])
            ->andReturn(['status' => 401, 'message' => 'error message']);

        $result = $this->sut->indexAction();

        $this->assertInstanceOf(ViewModel::class, $result);
        $this->assertEquals('auth/change-password', $result->getTemplate());
        $this->assertEquals(true, $result->getVariable('failed'));
        $this->assertEquals('error message', $result->getVariable('failureReason'));
    }
}
