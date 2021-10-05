<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Auth\Service\Auth;

use Common\Service\Cqrs\Command\CommandSender;
use Dvsa\Olcs\Auth\Service\Auth\ChangePasswordService;
use Dvsa\Olcs\Auth\Service\Auth\ChangePasswordServiceFactory;
use Dvsa\Olcs\Auth\Service\Auth\ResponseDecoderService;
use Interop\Container\ContainerInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * @see ChangePasswordServiceFactory
 */
class ChangePasswordServiceFactoryTest extends MockeryTestCase
{
    public function testServiceCreated(): void
    {
        $config = [
            'auth' => [
                'realm' => 'the-realm',
            ],
        ];

        $mockResponseDecoder = m::mock(ResponseDecoderService::class);
        $mockCommandSender = m::mock(CommandSender::class);

        $mockContainer = m::mock(ContainerInterface::class);
        $mockContainer->expects('get')->with('Config')->andReturn($config);
        $mockContainer->expects('get')->with('CommandSender')->andReturn($mockCommandSender);
        $mockContainer->expects('get')->with('Auth\ResponseDecoderService')->andReturn($mockResponseDecoder);

        $sut = new ChangePasswordServiceFactory();
        $service = $sut($mockContainer, ChangePasswordService::class);

        self::assertInstanceOf(ChangePasswordService::class, $service);
    }

    public function testMissingRealmConfig(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(ChangePasswordServiceFactory::MSG_MISSING_REALM);

        $config = [
            'auth' => [],
        ];

        $mockContainer = m::mock(ContainerInterface::class);
        $mockContainer->expects('get')->with('Config')->andReturn($config);

        $sut = new ChangePasswordServiceFactory();
        $sut($mockContainer, ChangePasswordService::class);
    }
}
