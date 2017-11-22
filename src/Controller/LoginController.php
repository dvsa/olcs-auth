<?php

namespace Dvsa\Olcs\Auth\Controller;

use Dvsa\Olcs\Auth\Controller\Traits\Authenticate;
use Dvsa\Olcs\Auth\Form\LoginForm;
use Zend\Http\Request;
use Zend\Http\Response;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

/**
 * Login Controller
 *
 * @method \Common\Controller\Plugin\CurrentUser currentUser()
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class LoginController extends AbstractActionController
{
    use Authenticate;

    /**
     * Login page
     *
     * @return Response|ViewModel
     */
    public function indexAction()
    {
        /** @var Request $request */
        $request = $this->getRequest();
        $identity = $this->currentUser()->getIdentity();

        if ($identity->isNotIdentified()) {
            throw new \Exception('Unable to retrieve identity');
        }

        $form = $this->getServiceLocator()->get('Helper\Form')->createFormWithRequest(LoginForm::class, $request);

        if ($this->isLoginFormPost($request) === false) {
            return $this->renderView($form);
        }

        $form->setData($request->getPost());

        if ($form->isValid() === false) {
            return $this->renderView($form);
        }

        $data = $form->getData();

        return $this->authenticate(
            $data['username'],
            $data['password'],
            function ($result) use ($form) {
                return $this->renderView(
                    $form,
                    true,
                    ($result['status'] == 401 ? $result['message'] : 'unknown-reason')
                );
            }
        );
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
        $view->setTemplate('auth/login');

        return $view;
    }

    /**
     * Check if the request is post, and whether it looks like it's a post from our login form
     *
     * @param Request $request Request
     *
     * @return bool
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
