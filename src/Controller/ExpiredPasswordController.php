<?php

namespace Dvsa\Olcs\Auth\Controller;

use Laminas\Http\Response;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Dvsa\Olcs\Auth\Service\Auth\ExpiredPasswordService;
use Dvsa\Olcs\Auth\Service\Auth\LoginService;
use Dvsa\Olcs\Auth\Form\ChangePasswordForm;

/**
 * Expired Password Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ExpiredPasswordController extends AbstractActionController
{
    /**
     * Expired password page
     *
     * @return Response|ViewModel
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

        if ($result['status'] != 200) {
            $this->getServiceLocator()->get('Helper\FlashMessenger')->addUnknownError();
            return $this->redirect()->toRoute('auth/login');
        }

        if (isset($result['tokenId'])) {
            return $this->getLoginService()
                ->login($result['tokenId'], $this->getResponse(), $this->params()->fromQuery('goto'));
        }

        $failureReason = preg_replace('/(Change Password\<BR\>\<\/BR\>)/', '', $result['header']);

        return $this->renderView($form, true, $failureReason);
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
        $authId = $this->params('authId');

        return $this->getExpiredPasswordService()->updatePassword(
            $authId,
            $data['oldPassword'],
            $data['newPassword'],
            $data['confirmPassword']
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
        $this->layout('auth/layout');
        $view = new ViewModel(['form' => $form, 'failed' => $failed, 'failureReason' => $failureReason]);
        $view->setTemplate('auth/expired-password');

        return $view;
    }

    /**
     * Get expired password service
     *
     * @return ExpiredPasswordService
     */
    private function getExpiredPasswordService()
    {
        return $this->getServiceLocator()->get('Auth\ExpiredPasswordService');
    }

    /**
     * Get the login service
     *
     * @return LoginService
     */
    private function getLoginService()
    {
        return $this->getServiceLocator()->get('Auth\LoginService');
    }
}
