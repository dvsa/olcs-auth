<?php

namespace Dvsa\Olcs\Auth\Service\Auth;

use Dvsa\Olcs\Auth\Service\Auth\Callback\ConfirmationCallback;
use Dvsa\Olcs\Auth\Service\Auth\Callback\PasswordCallback;
use Dvsa\Olcs\Auth\Service\Auth\Callback\Request;

/**
 * Expired Password Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ExpiredPasswordService extends AbstractRestService
{
    /**
     * Update password
     *
     * @param string $authId          Auth id
     * @param string $oldPassword     Old password
     * @param string $newPassword     New password
     * @param string $confirmPassword Confirm password
     *
     * @return array
     */
    public function updatePassword($authId, $oldPassword, $newPassword, $confirmPassword)
    {
        $response = $this->sendRequest($authId, $oldPassword, $newPassword, $confirmPassword);
        $result = $this->decodeContent($response);

        return $result;
    }

    /**
     * Build the request and send it
     *
     * @param string $authId          Auth id
     * @param string $oldPassword     Old password
     * @param string $newPassword     New password
     * @param string $confirmPassword Confirm password
     *
     * @return \Zend\Http\Response
     */
    private function sendRequest($authId, $oldPassword, $newPassword, $confirmPassword)
    {
        $request = $this->buildRequest($authId, $oldPassword, $newPassword, $confirmPassword);

        return $this->post('/json/authenticate', $request->toArray());
    }

    /**
     * Build request data
     *
     * @param string $authId          Auth id
     * @param string $oldPassword     Old password
     * @param string $newPassword     New password
     * @param string $confirmPassword Confirm password
     *
     * @return Request
     */
    private function buildRequest($authId, $oldPassword, $newPassword, $confirmPassword)
    {
        $request = new Request($authId, Request::STAGE_EXPIRED_PASSWORD);
        $request->addCallback(new PasswordCallback('Old Password', 'IDToken1', $oldPassword));
        $request->addCallback(new PasswordCallback('New Password', 'IDToken2', $newPassword));
        $request->addCallback(new PasswordCallback('Confirm Password', 'IDToken3', $confirmPassword));
        $request->addCallback(new ConfirmationCallback('IDToken4'));

        return $request;
    }
}
