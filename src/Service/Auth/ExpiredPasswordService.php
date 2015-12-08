<?php

/**
 * Expired Password Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
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
     * @param $oldPassword
     * @param $newPassword
     * @param $confirmPassword
     */
    public function updatePassword($authId, $oldPassword, $newPassword, $confirmPassword)
    {
        // First attempt has hashed password
        $response = $this->sendRequest($authId, $oldPassword, $newPassword, $confirmPassword);
        $result = $this->decodeContent($response);

        // If the password was wrong, attempt it again without the hashed password
        if (isset($result['header']) && strstr($result['header'], 'The password you entered is invalid')) {
            $response = $this->sendRequest($authId, $oldPassword, $newPassword, $confirmPassword, false);
            $result = $this->decodeContent($response);
        }

        return $result;
    }

    /**
     * Build the request and send it
     *
     * @param string $authId
     * @param string $username
     * @param string $password
     * @param bool $hash
     * @return \Zend\Http\Response
     */
    private function sendRequest($authId, $oldPassword, $newPassword, $confirmPassword, $hash = true)
    {
        $request = $this->buildRequest($authId, $oldPassword, $newPassword, $confirmPassword, $hash);

        return $this->post('/json/authenticate', $request->toArray());
    }

    /**
     * Build request data
     *
     * @param string $authId
     * @param string $oldPassword
     * @param string $newPassword
     * @param string $confirmPassword
     * @param boolean $hashOld
     * @return Request
     */
    private function buildRequest($authId, $oldPassword, $newPassword, $confirmPassword, $hashOld = false)
    {
        $request = new Request($authId, Request::STAGE_EXPIRED_PASSWORD);
        $request->addCallback(new PasswordCallback('Old Password', 'IDToken1', $oldPassword, $hashOld));
        $request->addCallback(new PasswordCallback('New Password', 'IDToken2', $newPassword));
        $request->addCallback(new PasswordCallback('Confirm Password', 'IDToken3', $confirmPassword));
        $request->addCallback(new ConfirmationCallback('IDToken4'));

        return $request;
    }
}
