<?php

/**
 * Logout Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Dvsa\Olcs\Auth\Controller;

use Dvsa\Olcs\Auth\Service\Auth\CookieService;
use Zend\Mvc\Controller\AbstractActionController;

/**
 * Logout Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class LogoutController extends AbstractActionController
{
    /**
     * Logout the user, and redirect to index
     *
     * @return \Zend\Http\Response
     */
    public function indexAction()
    {
        /** @var CookieService $cookieService */
        $cookieService = $this->getServiceLocator()->get('Auth\CookieService');

        $token = $cookieService->getCookie($this->getRequest());

        if (!empty($token)) {

            $this->getServiceLocator()->get('Auth\LogoutService')->logout($token);

            $cookieService->destroyCookie($this->getResponse());
        }

        return $this->redirect()->toRoute('dashboard');
    }
}
