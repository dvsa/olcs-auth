<?php

namespace Dvsa\Olcs\Auth\Controller;

use Dvsa\Olcs\Auth\Controller\Traits\Authenticate;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Dvsa\Olcs\Auth\Form\ResetPasswordForm;
use Dvsa\Olcs\Auth\Service\Auth\ResetPasswordService;

/**
 * Reset Password Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ResetPasswordController extends AbstractActionController
{
    use Authenticate;

    /**
     * Reset password
     *
     * @return \Zend\Http\Response|ViewModel
     */
    public function indexAction()
    {
        $request = $this->getRequest();

        $confirmationId = $this->params()->fromQuery('confirmationId');
        $tokenId = $this->params()->fromQuery('tokenId');
        $username = $this->params()->fromQuery('username');

        // Double check the combination of ids from the link in the email
        $result = $this->getResetPasswordService()->confirm($username, $confirmationId, $tokenId);
        if ($result['status'] !== 200) {
            $this->getServiceLocator()->get('Helper\FlashMessenger')->addErrorMessage('auth.forgot-password-expired');
            return $this->redirect()->toRoute('auth/forgot-password');
        }

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

        $result = $this->getResetPasswordService()
            ->resetPassword($username, $confirmationId, $tokenId, $data['newPassword']);

        if ($result['status'] == 200) {
            $this->getServiceLocator()->get('Helper\FlashMessenger')->addSuccessMessage('auth.reset-password.success');

            return $this->authenticate(
                $result['username'],
                $data['newPassword'],
                function () {
                    return $this->redirect()->toRoute('auth/login');
                }
            );
        }

        return $this->renderView($form, true, $result['message']);
    }

    /**
     * Render the view
     *
     * @param \Zend\Form\Form $form          Form
     * @param bool            $failed        Failed
     * @param string          $failureReason Failure reason
     *
     * @return ViewModel
     */
    private function renderView(\Zend\Form\Form $form, $failed = false, $failureReason = null)
    {
        $this->layout('auth/layout');
        $view = new ViewModel(['form' => $form, 'failed' => $failed, 'failureReason' => $failureReason]);
        $view->setTemplate('auth/reset-password');

        return $view;
    }

    /**
     * Get reset password service
     *
     * @return ResetPasswordService
     */
    private function getResetPasswordService()
    {
        return $this->getServiceLocator()->get('Auth\ResetPasswordService');
    }
}
