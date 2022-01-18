<?php

namespace Dvsa\Olcs\Auth\Controller;

use Dvsa\Olcs\Auth\Form\ChangePasswordForm;
use Laminas\View\Model\ViewModel;
use Dvsa\Olcs\Auth\Service\Auth\PasswordService;

class ChangePasswordController extends AbstractController
{
    /**
     * Forgot password page
     *
     * @return ViewModel
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

        $result = $this->updatePassword($form->getData());

        if ($result['flags']['success']) {
            $this->getServiceLocator()->get('Helper\FlashMessenger')
                ->addSuccessMessage('auth.change-password.success');

            // redir to my account
            return $this->redirectToMyAccount();
        }

        return $this->renderView($form, true, $result['messages'][0] ?? '');
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
     * @param array $data Data
     *
     * @return array
     */
    private function updatePassword(array $data)
    {
        return $this->getChangePasswordService()->updatePassword(
            $data['oldPassword'],
            $data['newPassword']
        );
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
     * @return PasswordService
     */
    private function getChangePasswordService(): PasswordService
    {
        return $this->getServiceLocator()->get(PasswordService::class);
    }
}
