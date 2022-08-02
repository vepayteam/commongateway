<?php

use app\services\payment\forms\CardRegForm;

class ServicesPaymentFormsCardRegFormTest extends \Codeception\Test\Unit
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
        $form = new CardRegForm();
        $form->type = 3;
        $form->partner = null;
        $form->extid = 'qwertyuiopasdfghjklzxcvbnmqwertyuiopasdaa';
        $form->successurl = 'test';
        $form->failurl = 'test';
        $form->cancelurl = 'test';
        $form->postbackurl = 'test';
        $form->postbackurl_v2 = 'test';
        $this->assertFalse($form->validate());
        $errors = $form->getErrors();
        $this->assertEquals('Тип регистрации не корректный', $errors['type'][0]);
        $this->assertEquals('Partner cannot be blank.', $errors['partner'][0]);
        $this->assertEquals('Extid should contain at most 40 characters.', $errors['extid'][0]);
        $this->assertEquals('Successurl is not a valid URL.', $errors['successurl'][0]);
        $this->assertEquals('Failurl is not a valid URL.', $errors['failurl'][0]);
        $this->assertEquals('Cancelurl is not a valid URL.', $errors['cancelurl'][0]);
        $this->assertEquals('Postbackurl is not a valid URL.', $errors['postbackurl'][0]);
        $this->assertEquals('Postbackurl V2 is not a valid URL.', $errors['postbackurl_v2'][0]);
        $form->type = 0;
        $form->partner = new app\models\payonline\Partner();
        $form->extid = 'qwertyuiopasdfghjklzxcvbnmqwertyuiopasd';
        $form->successurl = 'https://test.com';
        $form->failurl = 'https://test.com';
        $form->cancelurl = 'https://test.com';
        $form->postbackurl = 'https://test.com';
        $form->postbackurl_v2 = 'https://test.com';
        $this->assertTrue($form->validate());
    }

    public function testGetMutexKey()
    {
        $form = new CardRegForm();
        $form->extid = 'test';
        $this->tester->assertEquals('getPaySchetExttest', $form->getMutexKey());
    }
}