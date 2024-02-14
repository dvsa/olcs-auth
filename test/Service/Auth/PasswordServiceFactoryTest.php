<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Auth\Service\Auth;

use Common\Service\Cqrs\Command\CommandSender;
use Dvsa\Olcs\Auth\Service\Auth\PasswordService;
use Dvsa\Olcs\Auth\Service\Auth\PasswordServiceFactory;
use Dvsa\Olcs\Auth\Service\Auth\ResponseDecoderService;
use Psr\Container\ContainerInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * @see PasswordServiceFactory
 */
class PasswordServiceFactoryTest extends MockeryTestCase
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

        $sut = new PasswordServiceFactory();
        $service = $sut($mockContainer, PasswordService::class);

        self::assertInstanceOf(PasswordService::class, $service);
    }

    public function testMissingRealmConfig(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(PasswordServiceFactory::MSG_MISSING_REALM);

        $config = [
            'auth' => [],
        ];

        $mockContainer = m::mock(ContainerInterface::class);
        $mockContainer->expects('get')->with('Config')->andReturn($config);

        $sut = new PasswordServiceFactory();
        $sut($mockContainer, PasswordService::class);
    }
}
