<?php

/**
 * Reset Password Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Dvsa\Olcs\Auth\Controller;

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
    /**
     * Forgot password page
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

        $failed = false;
        $failureReason = '';

        if ($request->isPost()) {

            $post = $request->getPost();

            $form->setData($post);

            if ($form->isValid()) {
                $data = $form->getData();

                $result = $this->getResetPasswordService()
                    ->resetPassword($username, $confirmationId, $tokenId, $data['newPassword']);

                if ($result['status'] == 200) {
                    $this->getServiceLocator()->get('Helper\FlashMessenger')
                        ->addSuccessMessage('auth.reset-password.success');
                    return $this->redirect()->toRoute('auth/login');
                }

                $failed = true;
                $failureReason = $result['message'];
            }
        }

        $this->layout('auth/layout');
        $view = new ViewModel(
            [
                'form' => $form,
                'failed' => $failed,
                'failureReason' => $failureReason
            ]
        );
        $view->setTemplate('auth/reset-password');

        return $view;
    }

    /**
     * @return ResetPasswordService
     */
    private function getResetPasswordService()
    {
        return $this->getServiceLocator()->get('Auth\ResetPasswordService');
    }
}
