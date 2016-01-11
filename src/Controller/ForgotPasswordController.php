<?php

/**
 * Forgot Password Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Dvsa\Olcs\Auth\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Dvsa\Olcs\Auth\Form\ForgotPasswordForm;
use Zend\View\Model\ViewModel;
use Dvsa\Olcs\Auth\Service\Auth\ForgotPasswordService;
use Dvsa\Olcs\Transfer\Query\User\Pid;

/**
 * Forgot Password Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ForgotPasswordController extends AbstractActionController
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

        $form->setData($request->getPost());

        if ($form->isValid() === false) {
            return $this->renderView($form);
        }

        $data = $form->getData();

        $response = $this->handleQuery(Pid::create(['id' => $data['username']]));

        if ($response->isOk()) {
            $result = $this->getForgotPasswordService()->forgotPassword($response->getResult()['pid']);

            /**
             * Rather than redirecting, we show a different view in this case, that way the screen can only be shown
             * when a successful request has occurred
             */
            if ($result['status'] == 200) {
                $this->layout('auth/layout');
                $view = new ViewModel();
                $view->setTemplate('auth/confirm-forgot-password');

                return $view;
            } else {
                $message = $result['message'];
            }

        } else {
            if ($response->isClientError()) {
                // Mimic the OpenAM error message
                $message = 'User not found';
            } else {
                $message = 'unknown-error';
            }
        }

        return $this->renderView($form, true, $message);
    }

    /**
     * Render the view
     *
     * @param \Zend\Form\Form $form
     * @param bool $failed
     * @param string $failureReason
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
