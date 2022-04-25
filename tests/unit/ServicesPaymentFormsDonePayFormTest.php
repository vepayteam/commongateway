<?php

use app\services\payment\forms\DonePayForm;
use app\services\payment\models\PaySchet;

class ServicesPaymentFormsDonePayFormTest extends \Codeception\Test\Unit
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
        $form = new DonePayForm();
        $form->md = 0;
        $form->paRes = 0;
        $this->assertFalse($form->validate());
        $errors = $form->getErrors();
        $this->assertEquals('Md must be a string.', $errors['md'][0]);
        $this->assertEquals('Pa Res must be a string.', $errors['paRes'][0]);
        $form->IdPay = 1;
        $form->md = 'test';
        $form->paRes = 'test';
        $this->assertTrue($form->validate());
    }

    public function testPaySchetExist()
    {
        $form = new DonePayForm();
        $form->IdPay = 0;
        $this->tester->assertEquals(false,  $form->paySchetExist());
    }

    public function testGetPaySchet()
    {
        $form = new DonePayForm();
        $payschet = PaySchet::find()->orderBy('ID ASC')->one();
        $form->IdPay = $payschet['ID'];
        $this->tester->assertInstanceOf(PaySchet::class, $form->getPaySchet());

        $form = new DonePayForm();
        $form->IdPay = 0;
        $this->tester->assertEquals(null,  $form->getPaySchet());
    }
}
