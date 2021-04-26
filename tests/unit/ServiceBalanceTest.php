<?php

use app\models\mfo\MfoBalance;
use app\models\mfo\MfoReq;
use app\models\payonline\Partner;
use app\services\balance\Balance;
use app\services\balance\response\BalanceResponse;
use app\services\payment\banks\bank_adapter_requests\GetBalanceRequest;
use app\services\payment\banks\bank_adapter_responses\GetBalanceResponse;
use app\services\payment\models\Bank;

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
        $this->balance->setAttributes([
            'partner' => Partner::findOne(['ID' => 201])
        ]);
        $this->assertTrue($this->balance->validate());
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
            'partner' => Partner::findOne(['ID' => 201])
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
        $this->assertClassHasAttribute('banks', BalanceResponse::class);
        $this->assertClassHasAttribute('status', BalanceResponse::class);
        $this->assertClassHasAttribute('message', BalanceResponse::class);
    }

    public function testMfoBalanceInit()
    {
        $mfoBalance = new MfoBalance($this->partner);
        $this->assertInstanceOf(MfoBalance::class, $mfoBalance);
        $this->assertClassHasAttribute('Partner', MfoBalance::class);
    }

    public function testGetBalanceResponse()
    {
        $this->assertClassHasAttribute('balance', GetBalanceResponse::class);
    }
    public function testGetBalanceRequest()
    {
        $this->assertClassHasAttribute('currency', GetBalanceRequest::class);
        $this->assertClassHasAttribute('accounts', GetBalanceRequest::class);
    }

    public function testBalanceTraitFormatRequest()
    {
        $bank = $this->getMockBuilder(Bank::class)->getMock();
        $getBalanceRequest = $this->balance->formatRequest($bank);
        $this->assertInstanceOf(GetBalanceRequest::class, $getBalanceRequest);
    }
}
