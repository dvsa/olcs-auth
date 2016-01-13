<?php

/**
 * Reset Password Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Dvsa\Olcs\Auth\Service\Auth;

/**
 * Reset Password Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ResetPasswordService extends AbstractRestService
{
    /**
     * Confirm link is still valid
     *
     * @param $username
     * @param $confirmationId
     * @param $tokenId
     */
    public function confirm($username, $confirmationId, $tokenId)
    {
        $data = [
            'username' => $username,
            'tokenId' => $tokenId,
            'confirmationId' => $confirmationId
        ];

        return $this->decodeContent($this->post('json/users?_action=confirm', $data));
    }

    /**
     * Reset password
     *
     * @param $username
     * @param $confirmationId
     * @param $tokenId
     * @param $newPassword
     */
    public function resetPassword($username, $confirmationId, $tokenId, $newPassword)
    {
        $data = [
            // @todo Maybe remove all logic around hashing
            'userpassword' => HashService::hashPassword($newPassword),
            'username' => $username,
            'tokenId' => $tokenId,
            'confirmationId' => $confirmationId
        ];

        return $this->decodeContent($this->post('json/users?_action=forgotPasswordReset', $data));
    }
}
