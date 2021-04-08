<?php

use app\services\payment\banks\CauriAdapter;
use app\services\payment\forms\RefundPayForm;
use app\services\payment\models\PartnerBankGate;
use app\services\payment\models\PaySchet;

class CauriAdapterTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    protected function _before()
    {

    }

    protected function _after()
    {
        //
    }

    /**
     * vendor/bin/codecept run unit CauriAdapterTest
     */
    public function testSetGate()
    {
        $bankAdapter = new CauriAdapter();
        $partnerBankGate = new PartnerBankGate();
        $bankAdapter->setGate($partnerBankGate);
        $reflectionClass = new ReflectionClass(CauriAdapter::class);
        $gate = $reflectionClass->getProperty('gate');
        $gate->setAccessible(true);
        $this->assertInstanceOf(PartnerBankGate::class, $gate->getValue($bankAdapter));
    }

    public function testConvertStatusCompleted()
    {
        $bankAdapter = new CauriAdapter();
        $this->assertEquals(1, $bankAdapter->convertStatus('completed'));
    }

    public function testConvertStatusFailed()
    {
        $bankAdapter = new CauriAdapter();
        $this->assertEquals(2, $bankAdapter->convertStatus('failed'));
    }

    public function testFormatResolveUserRequest()
    {
        $bankAdapter = new CauriAdapter();
        $createPayForm = $this->getMockBuilder(PaySchet::class)->getMock();
        $this->assertArrayHasKey('identifier', $bankAdapter->formatResolveUserRequest($createPayForm));
        $this->assertArrayHasKey('ip', $bankAdapter->formatResolveUserRequest($createPayForm));
    }

    public function testRefundPay()
    {
        $refundPayForm = $this->getMockBuilder(RefundPayForm::class)->getMock();
        $bankAdapter = new CauriAdapter();
        $tKBankAdapterReflectionClass = new ReflectionClass(CauriAdapter::class);
        $refundPay = $tKBankAdapterReflectionClass->getMethod('refundPay');
        $refundPay->setAccessible(true);
        $this->expectException(yii\base\ErrorException::class);
        $this->assertEquals(null, $refundPay->invoke($bankAdapter, $refundPayForm));
    }
}
