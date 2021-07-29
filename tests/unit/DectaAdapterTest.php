<?php

use app\services\payment\forms\CreatePayForm;
use app\services\payment\forms\OutCardPayForm;
use app\services\payment\forms\OkPayForm;
use app\services\payment\forms\RefundPayForm;
use app\services\payment\models\PartnerBankGate;
use \app\services\payment\banks\DectaAdapter;

class DectaAdapterTest extends \Codeception\Test\Unit
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
    }

    public function testSetGate()
    {
        $dectaAdapter = new DectaAdapter();
        $partnerBankGate = new PartnerBankGate();
        $dectaAdapter->setGate($partnerBankGate);
        $dectaAdapterReflectionClass = new ReflectionClass(DectaAdapter::class);
        $gate = $dectaAdapterReflectionClass->getProperty('gate');
        $gate->setAccessible(true);
        $this->assertInstanceOf(PartnerBankGate::class, $gate->getValue($dectaAdapter));
    }

    public function testGetBankId()
    {
        $tKBankAdapter = new DectaAdapter();
        $this->assertIsInt($tKBankAdapter->getBankId());
    }

    public function testCreatePay()
    {
        $createPayForm = $this->getMockBuilder(CreatePayForm::class)->getMock();
        $tKBankAdapter = new DectaAdapter();
        $tKBankAdapterReflectionClass = new ReflectionClass(DectaAdapter::class);
        $checkStatusPay = $tKBankAdapterReflectionClass->getMethod('createPay');
        $checkStatusPay->setAccessible(true);
        $this->expectException(Error::class);
        $this->assertEquals(null, $checkStatusPay->invoke($tKBankAdapter, $createPayForm));
    }

    public function testCheckStatusPay()
    {
        $okPayForm = $this->getMockBuilder(OkPayForm::class)->getMock();
        $tKBankAdapter = new DectaAdapter();
        $tKBankAdapterReflectionClass = new ReflectionClass(DectaAdapter::class);
        $checkStatusPay = $tKBankAdapterReflectionClass->getMethod('checkStatusPay');
        $checkStatusPay->setAccessible(true);
        $this->expectException(yii\base\ErrorException::class);
        $this->assertEquals(null, $checkStatusPay->invoke($tKBankAdapter, $okPayForm));
    }

    public function testRefundPay()
    {
        $refundPayForm = $this->getMockBuilder(RefundPayForm::class)->getMock();
        $tKBankAdapter = new DectaAdapter();
        $tKBankAdapterReflectionClass = new ReflectionClass(DectaAdapter::class);
        $refundPay = $tKBankAdapterReflectionClass->getMethod('refundPay');
        $refundPay->setAccessible(true);
        $this->expectException(yii\base\ErrorException::class);
        $this->assertEquals(null, $refundPay->invoke($tKBankAdapter, $refundPayForm));
    }

    public function testOutCardPay()
    {
        $refundPayForm = $this->getMockBuilder(OutCardPayForm::class)->getMock();
        $tKBankAdapter = new DectaAdapter();
        $tKBankAdapterReflectionClass = new ReflectionClass(DectaAdapter::class);
        $refundPay = $tKBankAdapterReflectionClass->getMethod('outCardPay');
        $refundPay->setAccessible(true);
        $this->expectException(Error::class);
        $this->assertEquals(null, $refundPay->invoke($tKBankAdapter, $refundPayForm));
    }
}
