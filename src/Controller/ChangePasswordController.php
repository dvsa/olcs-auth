<?php

/**
 * Change Password Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Dvsa\Olcs\Auth\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Dvsa\Olcs\Auth\Form\ChangePasswordForm;
use Zend\View\Model\ViewModel;
use Dvsa\Olcs\Auth\Service\Auth\ChangePasswordService;

/**
 * Change Password Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ChangePasswordController extends AbstractActionController
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

        $form->setData($request->getPost());

        if ($form->isValid() === false) {
            return $this->renderView($form);
        }

        $result = $this->updatePassword($form->getData());

        if ($result['status'] == 200) {
            $this->getServiceLocator()->get('Helper\FlashMessenger')
                ->addSuccessMessage('auth.change-password.success');

            $config = $this->getServiceLocator()->get('Config');

            // redir to my account
            return $this->redirect()->toRouteAjax($config['my_account_route']);
        }

        return $this->renderView($form, true, $result['message']);
    }

    /**
     * Update password
     *
     * @param array $data
     * @return array
     */
    private function updatePassword(array $data)
    {
        return $this->getChangePasswordService()->updatePassword(
            $this->getRequest(),
            $data['oldPassword'],
            $data['newPassword']
        );
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
