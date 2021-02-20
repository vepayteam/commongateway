<?php

use app\services\payment\forms\AutoPayForm;

class ServicesPaymentFormsAutoPayFormTest extends \Codeception\Test\Unit
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
        $form = new AutoPayForm();
        $form->amount = 0;
        $form->extid = 0;
        $form->document_id = 0;
        $form->fullname = 0;
        $form->postbackurl = 'test';
        $form->descript = 0;
        $this->assertFalse($form->validate());
        $errors = $form->getErrors();
        $this->assertEquals('Amount must be no less than 1.', $errors['amount'][0]);
        $this->assertEquals('Нет такой карты', $errors['card'][0]);
        $this->assertEquals('Extid must be a string.', $errors['extid'][0]);
        $this->assertEquals('Document Id must be a string.', $errors['document_id'][0]);
        $this->assertEquals('Fullname must be a string.', $errors['fullname'][0]);
        $this->assertEquals('Postbackurl is not a valid URL.', $errors['postbackurl'][0]);
        $this->assertEquals('Descript must be a string.', $errors['descript'][0]);
        $form->amount = 1;
        $form->extid = 'test';
        $form->document_id = 'test';
        $form->fullname = 'test';
        $form->postbackurl = 'https://test.com';
        $form->descript = 'test';
        $autoPayFormReflectionClass = new ReflectionClass(AutoPayForm::class);
        $_card = $autoPayFormReflectionClass->getProperty('_card');
        $_card->setAccessible(true);
        $_card->setValue($form, new app\models\payonline\Cards());
        $this->assertTrue($form->validate());
    }

    public function testSerialize()
    {
        $form = new AutoPayForm();
        $form->amount = 1;
        $form->extid = 'test';
        $form->document_id = 'test';
        $form->fullname = 'test';
        $form->postbackurl = 'https://test.com';
        $form->descript = 'test';
        $form->card = 1;
        $serialize = $form->serialize();
        $dataSerialize= '{"amount":1,"extid":"test","document_id":"test","fullname":"test","postbackurl":"https://test.com","descript":"test","card":1,"partnerId":null,"paySchetId":null,"userId":null}';
        $this->tester->assertEquals($dataSerialize, $serialize);
    }

    public function testUnserialize()
    {
        $form = new AutoPayForm();
        $dataUnserialize = '{"amount":1,"extid":"test","document_id":"test","fullname":"test","postbackurl":"https://test.com","descript":"test","card":1,"partnerId":null,"paySchetId":null,"userId":null}';
        $form->unserialize($dataUnserialize);
        $this->tester->assertEquals(1, $form->amount);
        $this->tester->assertEquals('test', $form->extid);
        $this->tester->assertEquals('test', $form->document_id);
        $this->tester->assertEquals('test', $form->fullname);
        $this->tester->assertEquals('https://test.com', $form->postbackurl);
        $this->tester->assertEquals('test', $form->descript);
        $this->tester->assertEquals(1, $form->card);
    }
}