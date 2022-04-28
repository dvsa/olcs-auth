<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Auth\Controller;

use Common\Rbac\PidIdentityProvider;
use Dvsa\Olcs\Auth\Controller\ValidateController;
use Dvsa\Olcs\Auth\Service;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Laminas\View\Model\JsonModel;
use Laminas\View\Variables;
use ZfcRbac\Identity\IdentityProviderInterface;

/**
 * @see ValidateController
 */
class ValidateControllerTest extends MockeryTestCase
{
    /** @var  ValidateController */
    private $sut;

    /** @var  m\MockInterface */
    private $mockCookieSrv;
    /** @var  m\MockInterface */
    private $mockValidateSrv;

    public function setUp(): void
    {
        /** @var m\MockInterface mockCookieSrv */
        $this->mockCookieSrv = m::mock(Service\Auth\CookieService::class);
        $this->mockValidateSrv = m::mock(Service\Auth\ValidateService::class);
    }

    public function testIndexAction(): void
    {
        $returnedArray = ['response' => 'content'];
        $identityProvider = m::mock(IdentityProviderInterface::class);
        $identityProvider->expects('validateToken')->withNoArgs()->andReturn($returnedArray);

        $sut = new ValidateController($this->mockCookieSrv, $this->mockValidateSrv, $identityProvider);

        static::assertEquals($returnedArray, $sut->indexAction()->getVariables());
    }

    /**
     * @dataProvider  dpTestIndexActionOpenAm
     */
    public function testIndexActionOpenAm($token, $expect): void
    {
        $identityProvider = m::mock(PidIdentityProvider::class);
        $this->sut = new ValidateController($this->mockCookieSrv, $this->mockValidateSrv, $identityProvider);
        $request = $this->sut->getRequest();

        $this->mockCookieSrv->shouldReceive('getCookie')->with($request)->once()->andReturn($token);
        $this->mockValidateSrv
            ->shouldReceive('validate')
            ->times(empty($token) ? 0 : 1)
            ->andReturn(['unit_key' => 'EXPECT']);

        $action = $this->sut->indexAction();

        static::assertInstanceOf(JsonModel::class, $action);
        static::assertEquals($expect, $action->getVariables());
    }

    public function dpTestIndexActionOpenAm(): array
    {
        return [
            [
                'token' => 'unit_Token',
                'expect' => ['unit_key' => 'EXPECT'],
            ],
            [
                'token' => '',
                'expect' => new Variables(),
            ],
        ];
    }
}
