<?php

use app\services\payment\exceptions\BankAdapterResponseException;
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

    public function testReRequestingStatus()
    {
        $paySchet = $this->getMockBuilder(PaySchet::class)->getMock();
        $paySchet->ID = 111111;
        $class = $this->getMockBuilder(TKBankAdapter::class)
            ->setMethodsExcept(['curlXmlReq'])
            ->getMock();
//        $class =new TKBankAdapter();
//        $exception = null;
        $class = new TKBankAdapter();
        $this->expectException(BankAdapterResponseException::class);
        $class->reRequestingStatus($paySchet);
//        $this->expectException(new \yii\db\Exception('here', 200), $class->reRequestingStatus($paySchet));
//        try {
//            $class->reRequestingStatus($paySchet);
//        } catch (BankAdapterResponseException $e) {
//            $exception = $e;
//        }
//
//        $this->assertTrue($exception instanceof BankAdapterResponseException);
    }
}