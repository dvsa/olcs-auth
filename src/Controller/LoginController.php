<?php

/**
 * Login Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Dvsa\Olcs\Auth\Controller;

use Dvsa\Olcs\Auth\Form\LoginForm;
use Zend\Http\Request;
use Zend\Http\Response;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Dvsa\Olcs\Auth\Service\Auth\AuthenticationService;
use Dvsa\Olcs\Auth\Service\Auth\LoginService;

/**
 * Login Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class LoginController extends AbstractActionController
{
    /**
     * Login page
     *
     * @return Response|ViewModel
     */
    public function indexAction()
    {
        /** @var Request $request */
        $request = $this->getRequest();

        $form = $this->getServiceLocator()->get('Helper\Form')->createFormWithRequest(LoginForm::class, $request);

        $failed = false;
        $failureReason = null;

        if ($this->isLoginFormPost($request)) {

            $post = $request->getPost()->toArray();
            $form->setData($post);

            if ($form->isValid()) {

                $data = $form->getData();

                $result = $this->getAuthenticationService()->authenticate($data['username'], $data['password']);

                if ($result['status'] == 200) {

                    if (isset($result['tokenId'])) {

                        return $this->getLoginService()->login(
                            $result['tokenId'],
                            $this->getResponse(),
                            $this->params()->fromQuery('goto')
                        );
                    }

                    return $this->redirect()->toRoute('auth/expired-password', ['authId' => $result['authId']]);
                }

                if ($result['status'] == 401) {
                    $failed = true;
                    $failureReason = $result['message'];
                } else {
                    $failed = true;
                    $failureReason = 'unknown-reason';
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
        $view->setTemplate('auth/login');

        return $view;
    }

    /**
     * @return AuthenticationService
     */
    private function getAuthenticationService()
    {
        return $this->getServiceLocator()->get('Auth\AuthenticationService');
    }

    /**
     * @return LoginService
     */
    private function getLoginService()
    {
        return $this->getServiceLocator()->get('Auth\LoginService');
    }

    /**
     * Check if the request is post, and whether it looks like it's a post from our login form
     *
     * @param Request $request
     */
    private function isLoginFormPost(Request $request)
    {
        if ($request->isPost()) {
            $post = $request->getPost()->toArray();

            return isset($post['username']) && isset($post['password']);
        }

        return false;
    }
}
