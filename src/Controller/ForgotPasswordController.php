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

/**
 * Forgot Password Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ForgotPasswordController extends AbstractActionController
{
    /**
     * Forgot password page
     */
    public function indexAction()
    {
        $request = $this->getRequest();

        $form = $this->getServiceLocator()->get('Helper\Form')
            ->createFormWithRequest(ForgotPasswordForm::class, $request);

        $failed = false;
        $failureReason = '';

        if ($request->isPost()) {

            $post = $request->getPost();

            $form->setData($post);

            if ($form->isValid()) {
                $data = $form->getData();

                $result = $this->getForgotPasswordService()->forgotPassword($data['username']);

                /**
                 * Rather then redirecting, we show a different view in this case, that way the screen can only be shown
                 * when a successful request has occurred
                 */
                if ($result['status'] == 200) {

                    // @todo look up email address, if safe to do so
                    $this->layout('auth/layout');
                    $view = new ViewModel(['email' => 'EMAIL']);
                    $view->setTemplate('auth/confirm-forgot-password');

                    return $view;
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
        $view->setTemplate('auth/forgot-password');

        return $view;
    }

    /**
     * @return ForgotPasswordService
     */
    private function getForgotPasswordService()
    {
        return $this->getServiceLocator()->get('Auth\ForgotPasswordService');
    }
}
