<?php

use app\models\mfo\MfoBalance;
use app\models\mfo\MfoReq;
use app\models\payonline\Partner;
use app\services\balance\Balance;
use app\services\balance\response\BalanceResponse;
use app\services\payment\banks\bank_adapter_requests\GetBalanceRequest;
use app\services\payment\banks\bank_adapter_responses\GetBalanceResponse;
use app\services\payment\models\Bank;
use app\services\payment\models\PartnerBankGate;

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
            'partner' => Partner::findOne(['ID' => 123])
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
            'partner' => Partner::findOne(['ID' => 123])
        ]);
        $buildBalance = $this->balance->build($mfoRequest);
        $this->assertInstanceOf(BalanceResponse::class, $buildBalance);
        $this->assertIsString($buildBalance->message);
        $this->assertEquals([], $buildBalance->banks);
    }

    public function testBalanceResponse()
    {
        $this->assertClassHasAttribute('banks', BalanceResponse::class);
        $this->assertClassHasAttribute('status', BalanceResponse::class);
        $this->assertClassHasAttribute('message', BalanceResponse::class);
        $response = new BalanceResponse();
        $this->assertIsArray($response->banks);
        $this->assertEquals([], $response->banks);
        $this->assertEquals('', $response->message);
    }

    public function testGetBalanceResponse()
    {
        $this->assertClassHasAttribute('amount', GetBalanceResponse::class);
        $this->assertClassHasAttribute('currency', GetBalanceResponse::class);
        $this->assertClassHasAttribute('accountType', GetBalanceResponse::class);
        $this->assertClassHasAttribute('bank_name', GetBalanceResponse::class);
        $response = new GetBalanceResponse();
        $this->assertEquals('', $response->amount);
        $this->assertEquals('', $response->bank_name);
    }
    public function testGetBalanceRequest()
    {
        $this->assertClassHasAttribute('currency', GetBalanceRequest::class);
        $this->assertClassHasAttribute('accountNumber', GetBalanceRequest::class);
        $request = new GetBalanceRequest();
        $this->assertIsString($request->bankName);
        $this->assertEquals('', $request->accountNumber);
    }

    public function testBalanceTraitFormatRequest()
    {
        $bank = $this->getMockBuilder(Bank::class)->getMock();
        $gate = $this->getMockBuilder(PartnerBankGate::class)->getMock();
        $getBalanceRequest = $this->balance->formatRequest($gate, $bank);
        $this->assertInstanceOf(GetBalanceRequest::class, $getBalanceRequest);
    }
}
