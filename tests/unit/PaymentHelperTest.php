<?php

use app\services\payment\helpers\PaymentHelper;

class PaymentHelperTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    // tests
    public function testAmountConvertToFullAmount()
    {
        $class = new PaymentHelper();
        $testCase = $class->convertToFullAmount(1000);
        $testCase2 = $class->convertToFullAmount(1075);
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

    public function testFormatSum()
    {
        $class = new PaymentHelper();
        $testCase = $class->formatSum(10);
        $testCase2 = $class->formatSum(10000.00);
        $testCase3 = $class->formatSum(10.5);
        //Case 1
        $this->assertIsString($testCase);
        $this->assertEquals('10.00', $testCase);
        //Case 2
        $this->assertEquals('10 000.00', $testCase2);
        //Case 3
        $this->assertEquals('10.50', $testCase3);
    }

}
