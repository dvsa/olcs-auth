<?php

/**
 * Login Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Dvsa\Olcs\Auth\Controller;

use Dvsa\Olcs\Auth\Form\LoginForm;
use Dvsa\Olcs\Auth\Service\Auth\CookieService;
use Zend\Http\Request;
use Zend\Http\Response;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Dvsa\Olcs\Auth\Service\Auth\AuthenticationService;

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

        $authenticationFailed = false;
        $authenticationFailureReason = null;

        if ($this->isLoginFormPost($request)) {

            $post = $request->getPost()->toArray();
            $form->setData($post);

            if ($form->isValid()) {

                $data = $form->getData();

                $result = $this->getAuthenticationService()->authenticate($data['username'], $data['password']);

                if ($result['status'] === 200) {

                    if (isset($result['tokenId'])) {
                        /** @var CookieService $cookieService */
                        $cookieService = $this->getServiceLocator()->get('Auth\CookieService');
                        $cookieService->createTokenCookie($this->getResponse(), $result['tokenId']);

                        $goto = $this->params()->fromQuery('goto');

                        if ($goto !== null) {
                            return $this->redirect()->toUrl($goto);
                        }

                        return $this->redirect()->toRoute('index');
                    }

                    print '<pre>';
                    print_r($result);
                    exit;
                }

                if ($result['status'] === 401) {
                    $authenticationFailed = true;
                    $authenticationFailureReason = $result['message'];
                } else {
                    $authenticationFailed = true;
                    $authenticationFailureReason = 'unknown-reason';
                }
            }
        }

        $this->layout('auth/layout');
        $view = new ViewModel(
            [
                'form' => $form,
                'authenticationFailed' => $authenticationFailed,
                'authenticationFailureReason' => $authenticationFailureReason
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
