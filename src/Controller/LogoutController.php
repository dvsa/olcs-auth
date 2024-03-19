<?php

namespace Dvsa\Olcs\Auth\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\Session\Container;
use Laminas\Stdlib\RequestInterface;
use Laminas\Http\Response;
use Laminas\Http\Request;

/**
 * Class LogoutController
 */
class LogoutController extends AbstractActionController
{
    /**
     * @var RequestInterface
     */
    private $requestService;

    /**
     * @var Response
     */
    private $responseService;

    /**
     * @var bool
     */
    private $isSelfServe;

    /**
     * @var string
     */
    private $selfServeRedirectUrl;

    /**
     * @var Container
     */
    private $session;

    /**
     * LogoutController constructor.
     *
     * @param Request       $requestService       Laminas request service
     * @param Response      $responseService      Laminas response service
     * @param bool          $isSelfServe          Is the current user selfserve?
     * @param string        $selfServeRedirectUrl URL to redirect self serve user
     */
    public function __construct(
        Request $requestService,
        Response $responseService,
        $isSelfServe,
        $selfServeRedirectUrl,
        Container $session
    ) {
        $this->requestService = $requestService;
        $this->responseService = $responseService;
        $this->isSelfServe = $isSelfServe;
        $this->selfServeRedirectUrl = $selfServeRedirectUrl;
        $this->session = $session;
    }

    /**
     * Logout the user, and redirect to index or Gov site
     *
     * @return \Laminas\Http\Response
     */
    public function indexAction()
    {
        $this->session->exchangeArray([]);

        if ($this->isSelfServe) {
            // No need to add to config is it is only used once.
            return $this->redirect()->toUrl(
                $this->selfServeRedirectUrl
            );
        }
        return $this->redirect()->toRoute('auth/login/GET');
    }
}
