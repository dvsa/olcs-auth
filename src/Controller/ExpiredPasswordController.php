<?php

/**
 * Expired Password Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Dvsa\Olcs\Auth\Controller;

use Zend\Http\Response;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Dvsa\Olcs\Auth\Service\Auth\ExpiredPasswordService;
use Dvsa\Olcs\Auth\Service\Auth\LoginService;
use Dvsa\Olcs\Auth\Form\ExpiredPasswordForm;

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
            ->createFormWithRequest(ExpiredPasswordForm::class, $request);

        $failed = false;
        $failureReason = '';

        if ($request->isPost()) {

            $post = $request->getPost();

            $form->setData($post);

            if ($form->isValid()) {
                $data = $form->getData();
                $authId = $this->params('authId');

                $result = $this->getExpiredPasswordService()->updatePassword(
                    $authId,
                    $data['oldPassword'],
                    $data['newPassword'],
                    $data['confirmPassword']
                );

                if ($result['status'] == 200) {

                    if (isset($result['tokenId'])) {
                        return $this->getLoginService()->login(
                            $result['tokenId'],
                            $this->getResponse(),
                            $this->params()->fromQuery('goto')
                        );
                    }

                    $failed = true;
                    $failureReason = preg_replace('/(Change Password\<BR\>\<\/BR\>)/', '', $result['header']);
                } else {
                    $this->getServiceLocator()->get('Helper\FlashMessenger')->addUnknownError();
                    return $this->redirect()->toRoute('auth/login');
                }
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
        $view->setTemplate('auth/expired-password');

        return $view;
    }

    /**
     * @return ExpiredPasswordService
     */
    private function getExpiredPasswordService()
    {
        return $this->getServiceLocator()->get('Auth\ExpiredPasswordService');
    }

    /**
     * @return LoginService
     */
    private function getLoginService()
    {
        return $this->getServiceLocator()->get('Auth\LoginService');
    }
}
