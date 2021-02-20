<?php namespace forms;

use app\models\payonline\Partner;
use app\services\payment\forms\CardRegForm;
use Yii;

class CardRegFormTest extends \Codeception\Test\Unit
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

    // tests
    public function testValidate()
    {
//        $form = new CardRegForm();
//        $form->partner = Partner::findOne(['ID' => Yii::$app->params['testParams']['mfoPartnerId']]);
//        $this->assertTrue($form->validate());
//
//        $form->successurl = 'noturl';
//        $this->assertFalse($form->validate());
//
//        $form->successurl = 'http://example.com';
//        $form->failurl = 'noturl';
//        $this->assertFalse($form->validate());
//
//        $form->successurl = 'http://example.com';
//        $form->failurl = 'http://example.com';
//        $form->postbackurl = 'noturl';
//        $this->assertFalse($form->validate());
//
//        $form->successurl = 'http://example.com';
//        $form->failurl = 'http://example.com';
//        $form->postbackurl = 'http://example.com';
//        $form->postbackurl = 'http://example.com';
//
//        $this->assertTrue($form->validate());
    }
}
