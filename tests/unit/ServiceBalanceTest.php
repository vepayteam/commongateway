<?php

use app\models\mfo\MfoBalance;
use app\models\mfo\MfoReq;
use app\models\payonline\Partner;
use app\services\balance\Balance;
use app\services\balance\response\BalanceResponse;

class ServiceBalanceTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;
    /**
     * @var Balance
     */
    private $balance;
    /**
     * @var Partner|\PHPUnit\Framework\MockObject\MockObject
     */
    private $partner;

    protected function _before()
    {
        $this->partner = $this->getMockBuilder(Partner::class)->getMock();
        $this->balance = new Balance();
    }

    public function testBalanceServiceInit()
    {
        $this->assertInstanceOf(Balance::class, $this->balance);
        $this->assertClassHasAttribute('partner', Balance::class);
    }

    /**
     * @throws \app\services\payment\exceptions\GateException
     */
    public function testBalanceServiceBuild()
    {
        $mfoRequest = new MfoReq();
        $this->balance->setAttributes([
            'partner' => $this->partner
        ]);
        $buildBalance = $this->balance->build($mfoRequest);
        $this->assertInstanceOf(BalanceResponse::class, $buildBalance);
    }

    public function testBalanceTraitError()
    {
        $balanceError = $this->balance->balanceError('error message');
        $this->assertInstanceOf(BalanceResponse::class, $balanceError);
    }

    public function testBalanceResponse()
    {
        $response = new BalanceResponse();
        $this->assertIsObject($response);
        $this->assertClassHasAttribute('balance', BalanceResponse::class);
        $this->assertClassHasAttribute('hasError', BalanceResponse::class);
        $this->assertIsBool(false, $response->hasError);
        $this->assertIsBool(true, $response->hasError);
        $this->assertEquals(false, $response->hasError);
    }

    public function testMfoBalanceInit()
    {
        $mfoBalance = new MfoBalance($this->partner);
        $this->assertInstanceOf(MfoBalance::class, $mfoBalance);
        $this->assertClassHasAttribute('Partner', MfoBalance::class);
    }

}
