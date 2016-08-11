<?php

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
     * @param string $username       Username
     * @param string $confirmationId Confirmation id
     * @param string $tokenId        Token id
     *
     * @return array
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
     * @param string $username       Username
     * @param string $confirmationId Confirmation id
     * @param string $tokenId        Token id
     * @param string $newPassword    New password
     *
     * @return array
     */
    public function resetPassword($username, $confirmationId, $tokenId, $newPassword)
    {
        $data = [
            'userpassword' => $newPassword,
            'username' => $username,
            'tokenId' => $tokenId,
            'confirmationId' => $confirmationId
        ];

        return $this->decodeContent($this->post('json/users?_action=forgotPasswordReset', $data));
    }
}
