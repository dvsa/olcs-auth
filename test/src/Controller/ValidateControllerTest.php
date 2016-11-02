<?php

namespace Dvsa\OlcsTest\Auth\Controller;

use Dvsa\Olcs\Auth\Controller\ValidateController;
use Dvsa\Olcs\Auth\Service;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Zend\View\Model\JsonModel;
use Zend\View\Variables;

class ValidateControllerTest extends MockeryTestCase
{
    /** @var  ValidateController */
    private $sut;

    /** @var  m\MockInterface */
    private $mockCookieSrv;
    /** @var  m\MockInterface */
    private $mockValidateSrv;

    public function setUp()
    {
        /** @var m\MockInterface mockCookieSrv */
        $this->mockCookieSrv = m::mock(Service\Auth\CookieService::class);
        $this->mockValidateSrv = m::mock();

        /** @var \Zend\ServiceManager\ServiceManager $sm */
        $sm = m::mock(\Zend\ServiceManager\ServiceManager::class)->makePartial();
        $sm->setService('Auth\CookieService', $this->mockCookieSrv);
        $sm->setService(Service\Auth\ValidateService::class, $this->mockValidateSrv);

        /** @var \Zend\ServiceManager\ServiceLocatorInterface $sl */
        $sl = m::mock(\Zend\ServiceManager\ServiceLocatorInterface::class)
            ->shouldReceive('getServiceLocator')->once()->andReturn($sm)
            ->getMock();

        $this->sut = new ValidateController();
        $this->sut->createService($sl);
    }

    /**
     * @dataProvider  dpTestIndexAction
     */
    public function testIndexAction($token, $expect)
    {
        $request = $this->sut->getRequest();

        $this->mockCookieSrv->shouldReceive('getCookie')->with($request)->once()->andReturn($token);
        $this->mockValidateSrv
            ->shouldReceive('validate')
            ->times(empty($token) ? 0 : 1)
            ->andReturn(['unit_key' => 'EXPECT']);

        /** @var JsonModel $action */
        $action = $this->sut->indexAction();

        static::assertInstanceOf(JsonModel::class, $action);
        static::assertEquals($expect, $action->getVariables());
    }

    public function dpTestIndexAction()
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
