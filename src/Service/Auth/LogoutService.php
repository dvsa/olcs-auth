<?php

/**
 * Logout Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Dvsa\Olcs\Auth\Service\Auth;

use Zend\Http\Headers;

/**
 * Logout Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class LogoutService extends AbstractRestService
{
    /**
     * Delete the session from OpenAM
     *
     * @param string $tokenId
     * @return boolean
     */
    public function logout($tokenId)
    {
        $headers = new Headers();
        $headers->addHeaderLine('iplanetDirectoryPro', $tokenId);

        $response = $this->post('/json/sessions/?_action=logout', [], $headers);

        return $response->isOk();
    }
}
