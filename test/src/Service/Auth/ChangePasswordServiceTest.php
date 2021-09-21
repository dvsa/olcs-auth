<?php

namespace Dvsa\OlcsTest\Auth\Service\Auth;

use Common\Service\Cqrs\Command\CommandSender;
use Common\Service\Cqrs\Response as CqrsResponse;
use Dvsa\Olcs\Auth\Service\Auth\ChangePasswordService;
use Dvsa\Olcs\Auth\Service\Auth\ResponseDecoderService;
use Dvsa\Olcs\Transfer\Command\Auth\ChangePassword;
use Laminas\Http\Response as LaminasResponse;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * @see ChangePasswordService
 */
class ChangePasswordServiceTest extends MockeryTestCase
{
    public function testUpdatePassword()
    {
        $realm = 'the-realm';
        $oldPassword = 'old-password';
        $newPassword = 'new-password';

        $config = [
            'auth' => [
                'realm' => $realm,
            ],
        ];

        $data = [
            'realm' => $realm,
            'password' => $oldPassword,
            'newPassword' => $newPassword,
        ];

        $laminasResponse = m::mock(LaminasResponse::class);

        $cqrsResponse = m::mock(CqrsResponse::class);
        $cqrsResponse->expects('getHttpResponse')->withNoArgs()->andReturn($laminasResponse);

        $commandSender = m::mock(CommandSender::class);
        $commandSender->expects('send')->with(m::type(ChangePassword::class))->andReturnUsing(
            function (ChangePassword $changePasswordCmd) use ($data, $cqrsResponse) {
                $this->assertEquals($changePasswordCmd->getArrayCopy(), $data);
                return $cqrsResponse;
            }
        );

        $expected = [
            'status' => 200
        ];

        $responseDecoder = m::mock(ResponseDecoderService::class);
        $responseDecoder->expects('decode')->with($laminasResponse)->andReturn($expected);

        $sut = new ChangePasswordService($commandSender, $responseDecoder, $realm);
        $result = $sut->updatePassword($oldPassword, $newPassword);
        $this->assertEquals($expected, $result);
    }
}
