<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Auth\Controller;

use Common\Rbac\PidIdentityProvider;
use Dvsa\Olcs\Auth\ControllerFactory\ValidateControllerFactory;
use Dvsa\Olcs\Auth\Service\Auth\CookieService;
use Dvsa\Olcs\Auth\Service\Auth\ValidateService;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\JsonModel;
use LmcRbacMvc\Identity\IdentityProviderInterface;

/**
 * @see ValidateControllerFactory
 * ValidateController have action to validate is user session is active
 */
class ValidateController extends AbstractActionController
{
    private IdentityProviderInterface $identityProvider;

    public function __construct(
        IdentityProviderInterface $identityProvider
    ) {
        $this->identityProvider = $identityProvider;
    }

    /**
     * Validate is user session (token) is valid (active)
     */
    public function indexAction(): JsonModel
    {
        $respBody = $this->identityProvider->validateToken();
        return new JsonModel($respBody);
    }
}
