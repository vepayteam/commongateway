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
        $payschet = PaySchet::find()->orderBy('ID ASC')->one();
        $form->IdPay = $payschet['ID'];
        $this->assertTrue($form->validate());
    }

    public function testGetPaySchet()
    {
        $form = new OkPayForm();
        $payschet = PaySchet::find()->orderBy('ID ASC')->one();
        $form->IdPay = $payschet['ID'];
        $this->tester->assertInstanceOf(PaySchet::class, $form->getPaySchet());
    }
}