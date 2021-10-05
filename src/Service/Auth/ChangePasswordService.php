<?php

namespace Dvsa\Olcs\Auth\Service\Auth;

use Common\Service\Cqrs\Command\CommandSender;
use Dvsa\Olcs\Transfer\Command\Auth\ChangePassword;

class ChangePasswordService
{
    private CommandSender $commandSender;
    private ResponseDecoderService $responseDecoder;
    private string $realm;

    public function __construct(CommandSender $commandSender, ResponseDecoderService $responseDecoder, string $realm)
    {
        $this->commandSender = $commandSender;
        $this->responseDecoder = $responseDecoder;
        $this->realm = $realm;
    }

    /**
     * Update password
     *
     * @param string  $oldPassword Old password
     * @param string  $newPassword New password
     *
     * @return array
     */
    public function updatePassword($oldPassword, $newPassword): array
    {
        $data = [
            'realm' => $this->realm,
            'password' => $oldPassword,
            'newPassword' => $newPassword,
        ];

        $command = ChangePassword::create($data);

        return $this->responseDecoder->decode(
            $this->commandSender->send($command)->getHttpResponse()
        );
    }
}
