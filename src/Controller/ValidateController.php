<?php

namespace Dvsa\Olcs\Auth\Controller;

use Dvsa\Olcs\Auth\Service\Auth\CookieService;
use Dvsa\Olcs\Auth\Service\Auth\ValidateService;
use Zend\Http\Request;
use Zend\Http\Response;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

/**
 * ValidateController have action to validate is user session is active
 */
class ValidateController extends AbstractActionController implements FactoryInterface
{
    /** @var  CookieService */
    private $cookieSrv;
    /** @var  ValidateService */
    private $tokenValidateSrv;

    /**
     * Create an instance
     *
     * @param \Zend\Mvc\Controller\ControllerManager $serviceLocator Service Locator
     *
     * @return $this
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $sm = $serviceLocator->getServiceLocator();

        $this->cookieSrv =  $sm->get('Auth\CookieService');
        $this->tokenValidateSrv =  $sm->get(ValidateService::class);

        return $this;
    }

    /**
     * Validate is user session (token) is valid (active)
     *
     * @return JsonModel
     */
    public function indexAction()
    {
        /** @var \Zend\Http\Request $request */
        $request = $this->getRequest();

        $token = $this->cookieSrv->getCookie($request);

        $respBody = null;
        if (!empty($token)) {
            $respBody = $this->tokenValidateSrv->validate($token);
        }

        return new JsonModel($respBody);
    }
}
