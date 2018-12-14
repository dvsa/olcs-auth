<?php

/**
 * Authentication Service Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Dvsa\OlcsTest\Auth\Service\Auth;

use Dvsa\Olcs\Auth\Service\Auth\AuthenticationService;
use Dvsa\Olcs\Auth\Service\Auth\Exception\RuntimeException;
use Dvsa\Olcs\Auth\Service\Auth\ResponseDecoderService;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Zend\Http\Response;
use Zend\ServiceManager\ServiceManager;

/**
 * Authentication Service Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class AuthenticationServiceTest extends MockeryTestCase
{
    /**
     * @var AuthenticationService
     */
    private $sut;

    private $client;

    private $responseDecoder;

    public function setUp()
    {
        $this->client = m::mock();
        $this->responseDecoder = new ResponseDecoderService();

        $sm = m::mock(ServiceManager::class)->makePartial();
        $sm->setService('Auth\Client', $this->client);
        $sm->setService('Auth\ResponseDecoderService', $this->responseDecoder);

        $this->sut = new AuthenticationService();
        $this->sut->createService($sm);
    }

    public function testAuthenticateFailedBegin()
    {
        $this->expectException(RuntimeException::class);

        $username = 'foo';
        $password = 'bar';

        $response = new Response();
        $response->setStatusCode(400);

        $this->client->shouldReceive('post')
            ->with('/json/authenticate', [], null)
            ->andReturn($response);

        $this->sut->authenticate($username, $password);
    }

    public function testAuthenticate()
    {
        $username = 'foo';
        $password = 'bar';

        $response = new Response();
        $response->setStatusCode(200);
        $response->setContent('{"authId": "some-auth-id"}');

        $response2 = new Response();
        $response2->setStatusCode(200);
        $response2->setContent('{"tokenId": "some-token-id"}');

        $this->client->shouldReceive('post')
            ->with('/json/authenticate', [], null)
            ->andReturn($response);

        $request = [
            'authId' => 'some-auth-id',
            'stage' => 'LDAP1',
            'callbacks' => [
                [
                    'type' => 'NameCallback',
                    'output' => [['name' => 'prompt', 'value' => 'User Name:']],
                    'input' => [
                        [
                            'name' => 'IDToken1',
                            'value' => 'foo'
                        ]
                    ]
                ],
                [
                    'type' => 'PasswordCallback',
                    'output' => [['name' => 'prompt', 'value' => 'Password:']],
                    'input' => [
                        [
                            'name' => 'IDToken2',
                            'value' => 'bar'
                        ]
                    ]
                ]
            ]
        ];

        $this->client->shouldReceive('post')
            ->with('/json/authenticate', $request, null)
            ->andReturn($response2);

        $expected = [
            'tokenId' => 'some-token-id',
            'status' => 200
        ];

        $this->assertEquals($expected, $this->sut->authenticate($username, $password));
    }
}
