<?php

namespace Dvsa\OlcsTest\Auth\Service\Auth;

use Dvsa\Olcs\Auth\Service\Auth\ResponseDecoderService;
use Dvsa\Olcs\Auth\Service\Auth\ValidateService;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Zend\Http\Headers;
use Zend\Http\Response;
use Zend\ServiceManager\ServiceManager;

/**
 * @covers Dvsa\Olcs\Auth\Service\Auth\ValidateService
 */
class ValidateServiceTest extends MockeryTestCase
{
    /** @var  ValidateService */
    private $sut;

    /** @var  m\MockInterface */
    private $client;
    /** @var  m\MockInterface */
    private $mockCookieSrv;

    public function setUp()
    {
        $this->mockCookieSrv = m::mock();
        $this->client = m::mock();

        /** @var ServiceManager $sm */
        $sm = m::mock(ServiceManager::class)->makePartial();
        $sm
            ->setService('Auth\CookieService', $this->mockCookieSrv)
            ->setService('Auth\Client', $this->client)
            ->setService('Auth\ResponseDecoderService', new ResponseDecoderService());

        $this->sut = new ValidateService();
        $this->sut->createService($sm);
    }

    /**
     * @dataProvider dpTestValidate
     */
    public function testValidate($statusCode, $content, $expect)
    {
        $token = 'some-token';

        $mockResp = m::mock(Response::class)->makePartial()
            ->shouldReceive('getStatusCode')->atLeast(1)->andReturn($statusCode)
            ->shouldReceive('getContent')->atLeast(1)->andReturn($content)
            ->getMock();

        $this->mockCookieSrv->shouldReceive('getCookieName')
            ->andReturn('unit_CookieName');

        $this->client->shouldReceive('post')
            ->andReturnUsing(
                function ($url, $data, Headers $headers) use ($mockResp) {
                    static::assertEquals('/json/sessions/?_action=validate', $url);
                    static::assertEquals([], $data);
                    static::assertEquals('some-token', $headers->get('unit_CookieName')->getFieldValue());

                    return $mockResp;
                }
            );

        static::assertEquals($expect, $this->sut->validate($token));
    }

    public function dpTestValidate()
    {
        return [
            [
                'statusCode' => 500,
                'content' => '{"key": "EXPECT"}',
                'expect' => null,
            ],
            [
                'statusCode' => Response::STATUS_CODE_200,
                'content' => '{"key": "EXPECT"}',
                'expect' => [
                    'key' => 'EXPECT',
                    'status' => Response::STATUS_CODE_200,
                ],
            ],

        ];
    }
}
