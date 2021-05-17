<?php

use app\services\payment\helpers\PaymentHelper;

class PaymentHelperTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    // tests
    public function testAmountConvertToRub()
    {
        $class = new PaymentHelper();
        $testCase = $class->convertToRub(1000);
        $testCase2 = $class->convertToRub(1075);
        //Case 1
        $this->assertIsFloat($testCase);
        $this->assertEquals(10, $testCase);
        //Case 2
        $this->assertEquals(10.75, $testCase2);
    }

    public function testAmountConvertToPenny()
    {
        $class = new PaymentHelper();
        $testCase = $class->convertToPenny(10);
        $testCase2 = $class->convertToPenny(10.75);
        //Case 1
        $this->assertIsInt($testCase);
        $this->assertEquals(1000, $testCase);
        //Case 2
        $this->assertEquals(1075, $testCase2);
    }
}
