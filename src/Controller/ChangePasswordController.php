<?php

namespace Dvsa\Olcs\Auth\Controller;

use Common\Rbac\JWTIdentityProvider;
use Common\Service\Cqrs\Command\CommandSender;
use Dvsa\Olcs\Auth\Form\ChangePasswordForm;
use Dvsa\Olcs\Transfer\Command\Auth\ChangePassword;
use Dvsa\Olcs\Transfer\Result\Auth\ChangePasswordResult;
use Exception;
use Laminas\View\Model\ViewModel;
use Dvsa\Olcs\Auth\Service\Auth\ChangePasswordService;
use RuntimeException;

class ChangePasswordController extends AbstractController
{
    public const MESSAGE_BASE = "Expired Password Change Failed: %s";
    public const MESSAGE_RESULT_NOT_OK = 'Result is not ok';

    /**
     * Forgot password page
     *
     * @return ViewModel
     * @throws Exception
     */
    public function indexAction()
    {
        $request = $this->getRequest();

        $form = $this->getServiceLocator()->get('Helper\Form')
            ->createFormWithRequest(ChangePasswordForm::class, $request);

        if ($request->isPost() === false) {
            return $this->renderView($form);
        }

        if ($this->isButtonPressed('cancel')) {
            // redir to my account
            return $this->redirectToMyAccount();
        }

        $form->setData($request->getPost());

        if ($form->isValid() === false) {
            return $this->renderView($form);
        }

        $config = $this->serviceLocator->get('Config');
        $data = $form->getData();

        // TODO: VOL-2661 - Remove check and use updatePasswordCommand once OpenAM support is dropped
        if ($config['auth']['identity_provider'] !== JWTIdentityProvider::class) {
            $result =  $this->updatePasswordOpenAm(
                $data['oldPassword'],
                $data['newPassword']
            );
        } else {
            $result = $this->updatePasswordCommand($data['oldPassword'], $data['newPassword']);
        }

        if ($result->isValid()) {
            $this->getServiceLocator()->get('Helper\FlashMessenger')
                ->addSuccessMessage('auth.change-password.success');

            // redir to my account
            return $this->redirectToMyAccount();
        }

        return $this->renderView($form, true, $result->getMessage());
    }

    /**
     * Redirect to My Account page
     *
     * @return \Laminas\Http\Response
     */
    private function redirectToMyAccount()
    {
        $config = $this->getServiceLocator()->get('Config');

        // redir to my account
        return $this->redirect()->toRouteAjax($config['my_account_route']);
    }

    /**
     * Update password
     *
     * @param string $oldPassword
     * @param string $newPassword
     * @return ChangePasswordResult
     * @deprecated
     */
    private function updatePasswordOpenAM(string $oldPassword, string $newPassword): ChangePasswordResult
    {
        $result = $this->getChangePasswordService()->updatePassword(
            $this->getRequest(),
            $oldPassword,
            $newPassword
        );

        if ($result['status'] !== 200) {
            return new ChangePasswordResult(ChangePasswordResult::FAILURE, $result['message']);
        }

        return new ChangePasswordResult(ChangePasswordResult::SUCCESS);
    }

    /**
     * @throws Exception
     */
    private function updatePasswordCommand(string $oldPassword, string $newPassword): ChangePasswordResult
    {
        $command = ChangePassword::create([
            'password' => $oldPassword,
            'newPassword' => $newPassword
        ]);

        $commandSender = $this->getServiceLocator()->get('CommandSender');
        assert($commandSender instanceof CommandSender);

        $result = $commandSender->send($command);

        if (!$result->isOk()) {
            throw new RuntimeException(sprintf(static::MESSAGE_BASE, static::MESSAGE_RESULT_NOT_OK));
        }

        return ChangePasswordResult::fromArray($result->getResult()['flags'] ?? []);
    }

    /**
     * Render the view
     *
     * @param \Laminas\Form\Form $form          Form
     * @param bool            $failed        Failed
     * @param string          $failureReason Failure reason
     *
     * @return ViewModel
     */
    private function renderView(\Laminas\Form\Form $form, $failed = false, $failureReason = null)
    {
        $view = new ViewModel(['form' => $form, 'failed' => $failed, 'failureReason' => $failureReason]);
        $view->setTemplate('auth/change-password');

        return $view;
    }

    /**
     * Get change password service
     *
     * @return ChangePasswordService
     */
    private function getChangePasswordService()
    {
        return $this->getServiceLocator()->get('Auth\ChangePasswordService');
    }
}
