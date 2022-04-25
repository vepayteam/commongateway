<?php

use app\services\payment\forms\CreatePayForm;
use app\services\payment\models\PaySchet;

class ServicesPaymentFormsCreatePayFormTest extends \Codeception\Test\Unit
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
        $form = new CreatePayForm();
        $form->CardNumber = 'Не номер карты :)';
        $form->CardHolder = '';
        $form->CardExp = '';
        $form->CardCVC = 12;
        $form->IdPay = '';
        $form->Phone = 880012345;
        $form->Email = 'test';
        $form->LinkPhone = 2;
        $this->assertFalse($form->validate());
        $errors = $form->getErrors();
        $this->tester->assertEquals('Неверный номер карты', $errors['CardNumber'][0]);
        $this->tester->assertEquals('Заполните данные карты', $errors['CardHolder'][0]);
        $this->tester->assertEquals('Заполните данные карты', $errors['CardExp'][0]);
        $this->tester->assertEquals('Неверный CVC код', $errors['CardCVC'][0]);
        $this->tester->assertEquals('Заполните данные карты', $errors['IdPay'][0]);
        $this->tester->assertEquals('Неверный номер телефона', $errors['Phone'][0]);
        $this->tester->assertEquals('Неверный адрес почты', $errors['Email'][0]);
        $this->tester->assertEquals('Привязать номер к карте must be either "1" or "0".', $errors['LinkPhone'][0]);
        $form->CardNumber = 4314090010071979;
        $form->CardHolder = 'TEST TESOV';
        $form->CardExp = 1224;
        $form->CardCVC = 123;
        $form->IdPay = 1;
        $form->Phone = 8800123456;
        $form->Email = 'test@test.com';
        $form->LinkPhone = 0;
        $this->tester->assertTrue($form->validate());
    }

    public function testValidateIsTestCard()
    {
        $form = new CreatePayForm();
        $form->CardNumber = 5555555555555555;
        $form->validateIsTestCard();
        $errors = $form->getErrors();
        $this->tester->assertEquals('На тестовом контуре допускается использовать только тестовые карты', $errors['CardNumber'][0]);
    }

    public function testAttributeLabels()
    {
        $form = new CreatePayForm();
        $dataEquals = [
            'CardNumber' => 'Номер карты',
            'CardHolder' => 'Владелец',
            'CardExp' => 'Действует',
            'CardCVC' => 'CVC',
            'Phone' => 'Номер телефона',
            'LinkPhone' => 'Привязать номер к карте',
            'Email' => 'Почта для отправления чека'
        ];
        $this->tester->assertEquals($dataEquals, $form->attributeLabels());
    }

    public function testAfterValidate()
    {
        $form = new CreatePayForm();
        $form->CardExp = 1224;
        $form->afterValidate();
        $this->tester->assertEquals(12, $form->CardMonth);
        $this->tester->assertEquals(24, $form->CardYear);
    }

    public function testGetError()
    {
        $form = new CreatePayForm();
        $form->validate();
        $err = $form->GetError();
        $this->tester->assertEquals('Заполните данные карты', $err);
    }

    public function testGetPaySchet()
    {
        $form = new CreatePayForm();
        $payschet = PaySchet::find()->orderBy('IdPay ASC')->one();
        $form->IdPay = $payschet['IdPay'];
        $this->tester->assertInstanceOf(PaySchet::class, $form->getPaySchet());
    }

    public function testGetReturnUrl()
    {
        $form = new CreatePayForm();
        $form->IdPay = 0;
        $_SERVER['REQUEST_SCHEME'] = 'https';
        if (Yii::$app->params['DEVMODE'] == 'Y') {
            $this->tester->assertEquals($_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['SERVER_NAME'] . '/pay/orderdone?id=0', $form->getReturnUrl());
        } elseif (Yii::$app->params['TESTMODE'] == 'Y') {
            $this->tester->assertEquals('https://'.$_SERVER['SERVER_NAME'].'/pay/orderdone?id=0', $form->getReturnUrl());
        }
    }
}