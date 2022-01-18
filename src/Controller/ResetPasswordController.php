<?php

namespace Dvsa\Olcs\Auth\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Dvsa\Olcs\Auth\Form\ResetPasswordForm;
use Dvsa\Olcs\Auth\Service\Auth\PasswordService;

class ResetPasswordController extends AbstractActionController
{
    /**
     * Reset password
     *
     * @return \Laminas\Http\Response|ViewModel
     */
    public function indexAction()
    {
        $request = $this->getRequest();

        $form = $this->getServiceLocator()->get('Helper\Form')
            ->createFormWithRequest(ResetPasswordForm::class, $request);

        if ($request->isPost() === false) {
            return $this->renderView($form);
        }

        $form->setData($request->getPost());

        if ($form->isValid() === false) {
            return $this->renderView($form);
        }

        $data = $form->getData();

        $confirmationId = $this->params()->fromQuery('confirmationId');
        $tokenId = $this->params()->fromQuery('tokenId');
        $username = $this->params()->fromQuery('username');

        $result = $this->getResetPasswordService()
            ->resetPassword($username, $confirmationId, $tokenId, $data['newPassword']);

        if ($result['flags']['success']) {
            $this->getServiceLocator()->get('Helper\FlashMessenger')->addSuccessMessage('auth.reset-password.success');
            return $this->redirect()->toRoute('auth/login/GET');
        }

        return $this->renderView($form, true, $result['messages'][0] ?? '');
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
        $this->layout('auth/layout');
        $view = new ViewModel(['form' => $form, 'failed' => $failed, 'failureReason' => $failureReason]);
        $view->setTemplate('auth/reset-password');

        return $view;
    }

    /**
     * Get reset password service
     *
     * @return PasswordService
     */
    private function getResetPasswordService(): PasswordService
    {
        return $this->getServiceLocator()->get(PasswordService::class);
    }
}
