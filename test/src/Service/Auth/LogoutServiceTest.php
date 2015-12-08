<?php

/**
 * Logout Service Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Dvsa\OlcsTest\Auth\Service\Auth;

use Dvsa\Olcs\Auth\Service\Auth\LogoutService;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Zend\Http\Headers;
use Zend\Http\Response;
use Zend\ServiceManager\ServiceManager;

/**
 * Logout Service Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class LogoutServiceTest extends MockeryTestCase
{
    /**
     * @var LogoutService
     */
    private $sut;

    private $client;

    private $responseDecoder;

    public function setUp()
    {
        $this->client = m::mock();
        $this->responseDecoder = m::mock();

        $sm = m::mock(ServiceManager::class)->makePartial();
        $sm->setService('Auth\Client', $this->client);
        $sm->setService('Auth\ResponseDecoderService', $this->responseDecoder);

        $this->sut = new LogoutService();
        $this->sut->createService($sm);
    }

    public function testLogout()
    {
        $tokenId = 'some-token';

        $this->client->shouldReceive('post')
            ->with('/json/sessions/?_action=logout', [], m::type(Headers::class))
            ->andReturnUsing(
                function ($url, $data, Headers $headers) {
                    $this->assertEquals('some-token', $headers->get('iplanetDirectoryPro')->getFieldValue());

                    $response = new Response();
                    $response->setStatusCode(200);
                    return $response;
                }
            );

        $this->assertTrue($this->sut->logout($tokenId));
    }
}
