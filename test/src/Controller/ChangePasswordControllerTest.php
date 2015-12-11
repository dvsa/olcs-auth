<?php

/**
 * Change Password Controller Test
 */
namespace Dvsa\OlcsTest\Auth\Controller;

use Dvsa\Olcs\Auth\Controller\ChangePasswordController;
use Dvsa\Olcs\Auth\Form\ChangePasswordForm;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Zend\Form\Form;
use Zend\Http\Request as HttpRequest;
use Zend\Mvc\Controller\Plugin\Redirect;
use Zend\Mvc\Controller\PluginManager;
use Zend\ServiceManager\ServiceManager;
use Zend\View\Model\ViewModel;

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

    private $cookie;

    private $changePasswordService;

    private $flashMessenger;

    private $redirect;

    public function setUp()
    {
        $this->formHelper = m::mock();
        $this->cookie = m::mock();
        $this->changePasswordService = m::mock();
        $this->flashMessenger = m::mock();
        $this->redirect = m::mock(Redirect::class)->makePartial();

        $sm = m::mock(ServiceManager::class)->makePartial();
        $sm->setService('Helper\Form', $this->formHelper);
        $sm->setService('Auth\CookieService', $this->cookie);
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
        $request->setPost(new \Zend\Stdlib\Parameters($post));

        $result = $this->sut->indexAction();

        $this->assertInstanceOf(ViewModel::class, $result);
        $this->assertEquals('auth/change-password', $result->getTemplate());
    }

    public function testIndexActionForPostWithValidDataSuccess()
    {
        $post = [
            'oldPassword' => 'old-password',
            'newPassword' => 'new-password',
        ];
        $token = 'some-token';

        $form = m::mock(Form::class);
        $form->shouldReceive('setData')->once();
        $form->shouldReceive('isValid')->once()->andReturn(true);
        $form->shouldReceive('getData')->once()->andReturn($post);

        $this->formHelper->shouldReceive('createFormWithRequest')
            ->with(ChangePasswordForm::class, m::type(HttpRequest::class))
            ->andReturn($form);

        $request = $this->sut->getRequest();
        $request->setMethod('POST');
        $request->setPost(new \Zend\Stdlib\Parameters($post));

        $this->cookie->shouldReceive('getCookie')
            ->with($request)
            ->andReturn($token);

        $this->changePasswordService->shouldReceive('updatePassword')
            ->with($token, $post['oldPassword'], $post['newPassword'])
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
        $token = 'some-token';

        $form = m::mock(Form::class);
        $form->shouldReceive('setData')->once();
        $form->shouldReceive('isValid')->once()->andReturn(true);
        $form->shouldReceive('getData')->once()->andReturn($post);

        $this->formHelper->shouldReceive('createFormWithRequest')
            ->with(ChangePasswordForm::class, m::type(HttpRequest::class))
            ->andReturn($form);

        $request = $this->sut->getRequest();
        $request->setMethod('POST');
        $request->setPost(new \Zend\Stdlib\Parameters($post));

        $this->cookie->shouldReceive('getCookie')
            ->with($request)
            ->andReturn($token);

        $this->changePasswordService->shouldReceive('updatePassword')
            ->with($token, $post['oldPassword'], $post['newPassword'])
            ->andReturn(['status' => 401, 'message' => 'error message']);

        $result = $this->sut->indexAction();

        $this->assertInstanceOf(ViewModel::class, $result);
        $this->assertEquals('auth/change-password', $result->getTemplate());
        $this->assertEquals(true, $result->getVariable('failed'));
        $this->assertEquals('error message', $result->getVariable('failureReason'));
    }
}
