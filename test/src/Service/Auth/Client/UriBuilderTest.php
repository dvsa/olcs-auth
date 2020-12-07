<?php

/**
 * Uri Builder Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Dvsa\OlcsTest\Auth\Service\Auth\Client;

use Dvsa\Olcs\Auth\Service\Auth\Client\UriBuilder;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Dvsa\Olcs\Auth\Service\Auth\Exception;
use Laminas\ServiceManager\ServiceManager;

/**
 * Uri Builder Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class UriBuilderTest extends MockeryTestCase
{
    public function testBuildMissingConfig()
    {
        $this->expectException(Exception\RuntimeException::class);

        $config = [];

        $sm = m::mock(ServiceManager::class)->makePartial();
        $sm->setService('Config', $config);

        $sut = new UriBuilder();
        $sut->createService($sm);
    }

    public function testBuild()
    {
        $config = [
            'openam' => [
                'url' => 'olcs.openam'
            ]
        ];

        $sm = m::mock(ServiceManager::class)->makePartial();
        $sm->setService('Config', $config);

        $sut = new UriBuilder();
        $sut->createService($sm);

        $this->assertEquals('olcs.openam/foo/bar', $sut->build('foo/bar'));
    }

    public function testBuildWithRealm()
    {
        $config = [
            'openam' => [
                'url' => 'olcs.openam',
                'realm' => 'foo'
            ]
        ];

        $sm = m::mock(ServiceManager::class)->makePartial();
        $sm->setService('Config', $config);

        $sut = new UriBuilder();
        $sut->createService($sm);

        $this->assertEquals('olcs.openam/foo/bar?realm=foo', $sut->build('foo/bar'));
    }

    public function testBuildWithRealmAndQs()
    {
        $config = [
            'openam' => [
                'url' => 'olcs.openam',
                'realm' => 'foo'
            ]
        ];

        $sm = m::mock(ServiceManager::class)->makePartial();
        $sm->setService('Config', $config);

        $sut = new UriBuilder();
        $sut->createService($sm);

        $this->assertEquals('olcs.openam/foo/bar?foo=bar&realm=foo', $sut->build('foo/bar?foo=bar'));
    }
}
