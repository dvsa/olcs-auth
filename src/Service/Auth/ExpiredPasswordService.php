<?php

/**
 * Expired Password Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Dvsa\Olcs\Auth\Service\Auth;
use Dvsa\Olcs\Auth\Service\Auth\Callback\ConfirmationCallback;
use Dvsa\Olcs\Auth\Service\Auth\Callback\PasswordCallback;

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
        $data = $this->buildRequestData($authId, $oldPassword, $newPassword, $confirmPassword);

        $response = $this->post('json/authenticate', $data);

        return $this->decodeContent($response);
    }

    /**
     * Build request data
     *
     * @param string $authId
     * @param string $oldPassword
     * @param string $newPassword
     * @param string $confirmPassword
     * @param boolean $hashOld
     * @return array
     */
    private function buildRequestData($authId, $oldPassword, $newPassword, $confirmPassword, $hashOld = false)
    {
        $oldPasswordCallback = new PasswordCallback('Old Password', 'IDToken1', $oldPassword, $hashOld);
        $newPasswordCallback = new PasswordCallback('New Password', 'IDToken2', $newPassword);
        $confirmPasswordCallback = new PasswordCallback('Confirm Password', 'IDToken3', $confirmPassword);
        $confirmationCallback = new ConfirmationCallback();

        return [
            'authId' => $authId,
            'stage' => 'LDAP2',
            'callbacks' => [
                $oldPasswordCallback->toArray(),
                $newPasswordCallback->toArray(),
                $confirmPasswordCallback->toArray(),
                $confirmationCallback->toArray()
            ]
        ];
    }
}
