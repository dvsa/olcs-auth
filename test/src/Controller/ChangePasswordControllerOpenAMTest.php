<?php

/**
 * Change Password Controller Test
 */
namespace Dvsa\OlcsTest\Auth\Controller;

use Common\Rbac\PidIdentityProvider;
use Common\Service\Cqrs\Command\CommandSender;
use Dvsa\Olcs\Auth\Controller\ChangePasswordController;
use Dvsa\Olcs\Auth\Form\ChangePasswordForm;
use Laminas\Form\Form;
use Laminas\Http\Request as HttpRequest;
use Laminas\Mvc\Controller\Plugin\Redirect;
use Laminas\Mvc\Controller\PluginManager;
use Laminas\ServiceManager\ServiceManager;
use Laminas\View\Model\ViewModel;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Change Password Controller Test
 */
class ChangePasswordControllerOpenAMTest extends MockeryTestCase
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
                'identity_provider' => PidIdentityProvider::class
            ]
        ];
        $sm->setService('Config', $this->config);

        $pm = m::mock(PluginManager::class)->makePartial();
        $pm->setService('redirect', $this->redirect);

        $this->sut = new ChangePasswordController();
        $this->sut->setServiceLocator($sm);
        $this->sut->setPluginManager($pm);
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
        $this->config['auth']['identity_provider'] = PidIdentityProvider::class;

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
