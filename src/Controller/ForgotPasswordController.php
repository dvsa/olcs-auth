<?php

namespace Dvsa\Olcs\Auth\Controller;

use Dvsa\Olcs\Auth\Form\ForgotPasswordForm;
use Zend\View\Model\ViewModel;
use Dvsa\Olcs\Auth\Service\Auth\ForgotPasswordService;
use Dvsa\Olcs\Transfer\Query\User\Pid;

/**
 * Forgot Password Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ForgotPasswordController extends AbstractController
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
            ->createFormWithRequest(ForgotPasswordForm::class, $request);

        if ($request->isPost() === false) {
            return $this->renderView($form);
        }

        if ($this->isButtonPressed('cancel')) {
            return $this->redirect()->toRoute('auth/login');
        }

        $form->setData($request->getPost());

        if ($form->isValid() === false) {
            return $this->renderView($form);
        }

        $data = $form->getData();

        $response = $this->handleQuery(Pid::create(['id' => $data['username']]));

        if (!$response->isOk()) {
            if ($response->isClientError()) {
                // Mimic the OpenAM error message
                $message = 'User not found';
            } else {
                $message = 'unknown-error';
            }
            return $this->renderView($form, true, $message);
        }

        $pidResult = $response->getResult();

        if (!isset($pidResult['canResetPassword']) || $pidResult['canResetPassword'] !== true) {
            return $this->renderView($form, true, 'account-not-active');
        }

        $result = $this->getForgotPasswordService()->forgotPassword($data['username']);
        if ($result['status'] != 200) {
            return $this->renderView($form, true, $result['message']);
        }

        /**
         * Rather than redirecting, we show a different view in this case, that way the screen can only be shown
         * when a successful request has occurred
         */
        $this->layout('auth/layout');
        $view = new ViewModel();
        $view->setTemplate('auth/confirm-forgot-password');

        return $view;
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
        $view->setTemplate('auth/forgot-password');

        return $view;
    }

    /**
     * Get forgot password service
     *
     * @return ForgotPasswordService
     */
    private function getForgotPasswordService()
    {
        return $this->getServiceLocator()->get('Auth\ForgotPasswordService');
    }
}
