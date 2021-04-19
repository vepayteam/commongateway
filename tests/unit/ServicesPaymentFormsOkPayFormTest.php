<?php

use app\services\payment\forms\OkPayForm;
use app\services\payment\models\PaySchet;

class ServicesPaymentFormsOkPayFormTest extends \Codeception\Test\Unit
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

    public function testValidate()
    {
        $form = new OkPayForm();
        $form->IdPay = null;
        $this->assertFalse($form->validate());
        $errors = $form->getErrors();
        $this->assertEquals('Id Pay cannot be blank.', $errors['IdPay'][0]);
        $form->IdPay = 1;
        $this->assertTrue($form->validate());
    }

    public function testExistPaySchet()
    {
        $form = new OkPayForm();
        $form->IdPay = 0;
        $this->tester->assertEquals(false,  $form->existPaySchet());
    }

    public function testGetPaySchet()
    {
        $form = new OkPayForm();
        $form->IdPay = 1;
        $this->tester->assertInstanceOf(PaySchet::class, $form->getPaySchet());
    }
}