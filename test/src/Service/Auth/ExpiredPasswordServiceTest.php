<?php

/**
 * Expired Password Service Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Dvsa\OlcsTest\Auth\Service\Auth;

use Dvsa\Olcs\Auth\Service\Auth\ExpiredPasswordService;
use Dvsa\Olcs\Auth\Service\Auth\ResponseDecoderService;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Laminas\Http\Response;
use Laminas\ServiceManager\ServiceManager;

/**
 * Expired Password Service Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
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

        $sm = m::mock(ServiceManager::class)->makePartial();
        $sm->setService('Auth\Client', $this->client);
        $sm->setService('Auth\ResponseDecoderService', $this->responseDecoder);

        $this->sut = new ExpiredPasswordService();
        $this->sut->createService($sm);
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
