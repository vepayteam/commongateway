<?php

use app\services\ident\models\Ident;
use app\services\payment\banks\bank_adapter_responses\BaseResponse;
use app\services\payment\exceptions\BankAdapterResponseException;
use app\services\payment\forms\AutoPayForm;
use app\services\payment\forms\CreatePayForm;
use app\services\payment\forms\DonePayForm;
use app\services\payment\forms\OkPayForm;
use app\services\payment\forms\RefundPayForm;
use app\services\payment\models\PartnerBankGate;
use \app\services\payment\models\PaySchet;
use \app\services\payment\banks\TKBankAdapter;

class TKBankAdapterTest extends \Codeception\Test\Unit
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
        $tKBankAdapter = new TKBankAdapter();
        $partnerBankGate = new PartnerBankGate();
        $tKBankAdapter->setGate($partnerBankGate);
        $tKBankAdapterReflectionClass = new ReflectionClass(TKBankAdapter::class);
        $gate = $tKBankAdapterReflectionClass->getProperty('gate');
        $gate->setAccessible(true);
        $this->assertInstanceOf(PartnerBankGate::class, $gate->getValue($tKBankAdapter));
    }

    public function testGetBankId()
    {
        $tKBankAdapter = new TKBankAdapter();
        $this->assertIsInt($tKBankAdapter->getBankId());
    }

    public function testCreateTisket()
    {
        $tKBankAdapter = new TKBankAdapter();
        $tKBankAdapterReflectionClass = new ReflectionClass(TKBankAdapter::class);
        $createTisket = $tKBankAdapterReflectionClass->getMethod('createTisket');
        $createTisket->setAccessible(true);
        $this->assertEquals(['tisket' => '', 'recurrent' => 0, 'url' => ''], $createTisket->invoke($tKBankAdapter, ['IdPay' => null]));
    }

    public function testCheckStatusOrder()
    {
        $tKBankAdapter = new TKBankAdapter();
        $partnerBankGate = new PartnerBankGate();
        $partnerBankGate->Login = '';
        $tKBankAdapter->setGate($partnerBankGate);
        $paySchet = $this->getMockBuilder(PaySchet::class)->getMock();
        $tKBankAdapterReflectionClass = new ReflectionClass(TKBankAdapter::class);
        $checkStatusOrder = $tKBankAdapterReflectionClass->getMethod('checkStatusOrder');
        $checkStatusOrder->setAccessible(true);
        $this->assertEquals(['state' => 0], $checkStatusOrder->invoke($tKBankAdapter, $paySchet, null));
    }

    public function testConvertState()
    {
        $tKBankAdapter = new TKBankAdapter();
        $tKBankAdapterReflectionClass = new ReflectionClass(TKBankAdapter::class);
        $convertState = $tKBankAdapterReflectionClass->getMethod('convertState');
        $convertState->setAccessible(true);
        $this->assertEquals(0, $convertState->invoke($tKBankAdapter, []));
    }

    public function testReRequestingStatus()
    {
        $tKBankAdapter = new TKBankAdapter();
        $partnerBankGate = new PartnerBankGate();
        $partnerBankGate->Login = '';
        $tKBankAdapter->setGate($partnerBankGate);
        $this->expectException(BankAdapterResponseException::class);
        $paySchet = $this->getMockBuilder(PaySchet::class)->getMock();
        $tKBankAdapter->reRequestingStatus($paySchet);
    }

    public function testReRequestingStatusMock()
    {
        $paySchet = $this->getMockBuilder(PaySchet::class)->getMock();
        $tKBankAdapter = $this->getMockBuilder(TKBankAdapter::class)
            ->setMethodsExcept(['curlXmlReq'])
            ->getMock();
        $tKBankAdapter->reRequestingStatus($paySchet);
    }

    public function testConvertStatePay()
    {
        $tKBankAdapter = new TKBankAdapter();
        $tKBankAdapterReflectionClass = new ReflectionClass(TKBankAdapter::class);
        $convertStatePay = $tKBankAdapterReflectionClass->getMethod('convertStatePay');
        $convertStatePay->setAccessible(true);
        $this->assertEquals(0, $convertStatePay->invoke($tKBankAdapter, []));
    }

    public function testConvertStateCard()
    {
        $tKBankAdapter = new TKBankAdapter();
        $tKBankAdapterReflectionClass = new ReflectionClass(TKBankAdapter::class);
        $convertStateCard = $tKBankAdapterReflectionClass->getMethod('convertStateCard');
        $convertStateCard->setAccessible(true);
        $this->assertEquals(0, $convertStateCard->invoke($tKBankAdapter, []));
    }

    public function testConvertStateOut()
    {
        $tKBankAdapter = new TKBankAdapter();
        $tKBankAdapterReflectionClass = new ReflectionClass(TKBankAdapter::class);
        $convertStateOut = $tKBankAdapterReflectionClass->getMethod('convertStateOut');
        $convertStateOut->setAccessible(true);
        $this->assertEquals(0, $convertStateOut->invoke($tKBankAdapter, []));
    }

    public function testLogArr()
    {
        $tKBankAdapter = new TKBankAdapter();
        $tKBankAdapterReflectionClass = new ReflectionClass(TKBankAdapter::class);
        $logArr = $tKBankAdapterReflectionClass->getMethod('logArr');
        $logArr->setAccessible(true);
        $this->assertEquals("Array\n(\n)\n", $logArr->invoke($tKBankAdapter, []));
    }

    public function testParseAns()
    {
        $tKBankAdapter = new TKBankAdapter();
        $tKBankAdapterReflectionClass = new ReflectionClass(TKBankAdapter::class);
        $parseAns = $tKBankAdapterReflectionClass->getMethod('parseAns');
        $parseAns->setAccessible(true);
        $this->assertEquals(['test' => 'TEST'], $parseAns->invoke($tKBankAdapter, ['TEST' => 'TEST']));
    }

    public function testArrayChangeKeyCaseRecursive()
    {
        $tKBankAdapter = new TKBankAdapter();
        $tKBankAdapterReflectionClass = new ReflectionClass(TKBankAdapter::class);
        $array_change_key_case_recursive = $tKBankAdapterReflectionClass->getMethod('array_change_key_case_recursive');
        $array_change_key_case_recursive->setAccessible(true);
        $this->assertEquals(['test' => 'TEST'], $array_change_key_case_recursive->invoke($tKBankAdapter, ['TEST' => 'TEST'], CASE_LOWER));
    }

    public function testHmacSha1()
    {
        $tKBankAdapter = new TKBankAdapter();
        $tKBankAdapterReflectionClass = new ReflectionClass(TKBankAdapter::class);
        $HmacSha1 = $tKBankAdapterReflectionClass->getMethod('HmacSha1');
        $HmacSha1->setAccessible(true);
        $this->assertEquals('DJRRXBXlCVuKh6ULoN87847QX+Y=', $HmacSha1->invoke($tKBankAdapter, 'test', 'test'));
    }

    public function testCreatePay3DSv1()
    {
        $createPayForm = $this->getMockBuilder(CreatePayForm::class)->getMock();
        $tKBankAdapter = new TKBankAdapter();
        $tKBankAdapterReflectionClass = new ReflectionClass(TKBankAdapter::class);
        $createPay3DSv1 = $tKBankAdapterReflectionClass->getMethod('createPay3DSv1');
        $createPay3DSv1->setAccessible(true);
        $this->expectException(yii\base\ErrorException::class);
        $this->assertEquals(null, $createPay3DSv1->invoke($tKBankAdapter, $createPayForm, null));
    }

    public function testConfirm()
    {
        $donePayForm = $this->getMockBuilder(DonePayForm::class)->getMock();
        $tKBankAdapter = new TKBankAdapter();
        $this->expectException(yii\base\ErrorException::class);
        $this->assertEquals('', $tKBankAdapter->confirm($donePayForm));
    }

    public function testConfirmBy3DSv1()
    {
        $donePayForm = $this->getMockBuilder(DonePayForm::class)->getMock();
        $tKBankAdapter = new TKBankAdapter();
        $tKBankAdapterReflectionClass = new ReflectionClass(TKBankAdapter::class);
        $confirmBy3DSv1 = $tKBankAdapterReflectionClass->getMethod('confirmBy3DSv1');
        $confirmBy3DSv1->setAccessible(true);
        $this->expectException(yii\base\ErrorException::class);
        $this->assertEquals(null, $confirmBy3DSv1->invoke($tKBankAdapter, $donePayForm));
    }

    public function testConfirmBy3DSv2()
    {
        $donePayForm = $this->getMockBuilder(DonePayForm::class)->getMock();
        $tKBankAdapter = new TKBankAdapter();
        $tKBankAdapterReflectionClass = new ReflectionClass(TKBankAdapter::class);
        $confirmBy3DSv2 = $tKBankAdapterReflectionClass->getMethod('confirmBy3DSv2');
        $confirmBy3DSv2->setAccessible(true);
        $this->expectException(yii\base\ErrorException::class);
        $this->assertEquals(null, $confirmBy3DSv2->invoke($tKBankAdapter, $donePayForm));
    }

    public function testValidateBy3DSv2()
    {
        $donePayForm = $this->getMockBuilder(DonePayForm::class)->getMock();
        $tKBankAdapter = new TKBankAdapter();
        $tKBankAdapterReflectionClass = new ReflectionClass(TKBankAdapter::class);
        $validateBy3DSv2 = $tKBankAdapterReflectionClass->getMethod('validateBy3DSv2');
        $validateBy3DSv2->setAccessible(true);
        $this->expectException(yii\base\ErrorException::class);
        $this->assertEquals(null, $validateBy3DSv2->invoke($tKBankAdapter, $donePayForm));
    }

    public function testFinishBy3DSv2()
    {
        $donePayForm = $this->getMockBuilder(DonePayForm::class)->getMock();
        $tKBankAdapter = new TKBankAdapter();
        $tKBankAdapterReflectionClass = new ReflectionClass(TKBankAdapter::class);
        $finishBy3DSv2 = $tKBankAdapterReflectionClass->getMethod('finishBy3DSv2');
        $finishBy3DSv2->setAccessible(true);
        $this->expectException(yii\base\ErrorException::class);
        $this->assertEquals(null, $finishBy3DSv2->invoke($tKBankAdapter, $donePayForm));
    }

    public function testCheckStatusPay()
    {
        $okPayForm = $this->getMockBuilder(OkPayForm::class)->getMock();
        $tKBankAdapter = new TKBankAdapter();
        $tKBankAdapterReflectionClass = new ReflectionClass(TKBankAdapter::class);
        $checkStatusPay = $tKBankAdapterReflectionClass->getMethod('checkStatusPay');
        $checkStatusPay->setAccessible(true);
        $this->expectException(yii\base\ErrorException::class);
        $this->assertEquals(null, $checkStatusPay->invoke($tKBankAdapter, $okPayForm));
    }

    public function testRecurrentPay()
    {
        $autoPayForm = $this->getMockBuilder(AutoPayForm::class)->getMock();
        $tKBankAdapter = new TKBankAdapter();
        $tKBankAdapterReflectionClass = new ReflectionClass(TKBankAdapter::class);
        $recurrentPay = $tKBankAdapterReflectionClass->getMethod('recurrentPay');
        $recurrentPay->setAccessible(true);
        $this->expectException(yii\base\ErrorException::class);
        $this->assertEquals(null, $recurrentPay->invoke($tKBankAdapter, $autoPayForm));
    }

    public function testRefundPay()
    {
        $refundPayForm = $this->getMockBuilder(RefundPayForm::class)->getMock();
        $tKBankAdapter = new TKBankAdapter();
        $tKBankAdapterReflectionClass = new ReflectionClass(TKBankAdapter::class);
        $refundPay = $tKBankAdapterReflectionClass->getMethod('refundPay');
        $refundPay->setAccessible(true);
        $this->expectException(yii\base\ErrorException::class);
        $this->assertEquals(null, $refundPay->invoke($tKBankAdapter, $refundPayForm));
    }

    public function testIdentInit()
    {
        /** @var Ident $ident */
        $ident = $this->getMockBuilder(\app\services\ident\models\Ident::class)->getMock();
        $tkBankAdapter = new TKBankAdapter();
        $identInitResponse = $tkBankAdapter->identInit($ident);

        $this->assertTrue($identInitResponse->status == BaseResponse::STATUS_DONE);
    }

    public function testIdentGetStatus()
    {
        /** @var Ident $ident */
        $ident = $this->getMockBuilder(\app\services\ident\models\Ident::class)->getMock();
        $gate = new PartnerBankGate();
        $gate->Login = 'test';
        $gate->Token = 'test';
        $gate->Password = 'test';
        $tkBankAdapter = new TKBankAdapter();
        $tkBankAdapter->setGate($gate);
        $identInitResponse = $tkBankAdapter->identGetStatus($ident);

        $this->assertTrue($identInitResponse->status == BaseResponse::STATUS_DONE);
    }
}
