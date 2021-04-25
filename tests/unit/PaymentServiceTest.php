<?php 
class PaymentServiceTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;
    /** @var \app\services\payment\PaymentService */
    protected $paymentService;
    
    protected function _before()
    {
        $this->paymentService = Yii::$container->get('PaymentService');
    }

    protected function _after()
    {
    }

    // tests
    public function testGetSbpBankReceive()
    {
        $data = $this->paymentService->getSbpBankReceive();
        $this->assertTrue(
            is_array($data)
            && array_key_exists('fpsMembers', $data)
            && count($data['fpsMembers']) > 0
        );
    }
}
