<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Auth\Controller;

use Common\Rbac\PidIdentityProvider;
use Dvsa\Olcs\Auth\ControllerFactory\ValidateControllerFactory;
use Dvsa\Olcs\Auth\Service\Auth\CookieService;
use Dvsa\Olcs\Auth\Service\Auth\ValidateService;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\JsonModel;
use ZfcRbac\Identity\IdentityProviderInterface;

/**
 * @see ValidateControllerFactory
 * ValidateController have action to validate is user session is active
 */
class ValidateController extends AbstractActionController
{
    private CookieService $cookieSrv;
    private ValidateService $tokenValidateSrv;
    private IdentityProviderInterface $identityProvider;

    public function __construct(
        CookieService $cookieService,
        ValidateService $validateService,
        IdentityProviderInterface $identityProvider
    ) {
        $this->cookieSrv =  $cookieService;
        $this->tokenValidateSrv =  $validateService;
        $this->identityProvider = $identityProvider;
    }

    /**
     * Validate is user session (token) is valid (active)
     */
    public function indexAction(): JsonModel
    {
        if ($this->identityProvider instanceof PidIdentityProvider) {
            $request = $this->getRequest();

            $token = $this->cookieSrv->getCookie($request);

            $respBody = null;
            if (!empty($token)) {
                $respBody = $this->tokenValidateSrv->validate($token);
            }

            return new JsonModel($respBody);
        }

        $respBody = $this->identityProvider->validateToken();
        return new JsonModel($respBody);
    }
}
