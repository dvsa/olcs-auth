<?php

namespace Dvsa\OlcsTest\Auth\Service\Auth;

use Dvsa\Olcs\Auth\Service\Auth\ExpiredPasswordService;
use Dvsa\Olcs\Auth\Service\Auth\ResponseDecoderService;
use Interop\Container\Containerinterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Laminas\Http\Response;

class ExpiredPasswordServiceTest extends MockeryTestCase
{
    /**
     * @var ExpiredPasswordService
     */
    private $sut;

    private $client;

    private $responseDecoder;

    public function setUp(): void
    {
        $this->client = m::mock();
        $this->responseDecoder = new ResponseDecoderService();

        $container = m::mock(ContainerInterface::class);
        $container->expects('get')->with('Auth\Client')->andReturn($this->client);
        $container->expects('get')->with('Auth\ResponseDecoderService')->andReturn($this->responseDecoder);

        $this->sut = new ExpiredPasswordService();
        $this->sut->__invoke($container, ExpiredPasswordService::class);
    }

    public function testUpdatePassword()
    {
        $data = [
            'authId' => 'auth-id',
            'stage' => 'LDAP2',
            'callbacks' => [
                [
                    'type' => 'PasswordCallback',
                    'output' => [['name' => 'prompt', 'value' => 'Old Password']],
                    'input' => [
                        [
                            'name' => 'IDToken1',
                            'value' => 'old-password'
                        ]
                    ]
                ],
                [
                    'type' => 'PasswordCallback',
                    'output' => [['name' => 'prompt', 'value' => 'New Password']],
                    'input' => [
                        [
                            'name' => 'IDToken2',
                            'value' => 'new-password'
                        ]
                    ]
                ],
                [
                    'type' => 'PasswordCallback',
                    'output' => [['name' => 'prompt', 'value' => 'Confirm Password']],
                    'input' => [
                        [
                            'name' => 'IDToken3',
                            'value' => 'new-password'
                        ]
                    ]
                ],
                [
                    'type' => 'ConfirmationCallback',
                    'output' => [
                        ['name' => 'prompt', 'value' => ''],
                        ['name' => 'messageType', 'value' => 0],
                        ['name' => 'options', 'value' => ['Submit', 'Cancel']],
                        ['name' => 'optionType', 'value' => -1],
                        ['name' => 'defaultOption', 'value' => 0]
                    ],
                    'input' => [['name' => 'IDToken4', 'value' => 0]]
                ]
            ]
        ];

        $response = new Response();
        $response->setStatusCode(200);
        $response->setContent('{"tokenId": "bar"}');

        $this->client->shouldReceive('post')
            ->with('/json/authenticate', $data, null)
            ->andReturn($response);

        $result = $this->sut->updatePassword('auth-id', 'old-password', 'new-password', 'new-password');

        $expected = [
            'tokenId' => 'bar',
            'status' => 200
        ];

        $this->assertEquals($expected, $result);
    }
}
