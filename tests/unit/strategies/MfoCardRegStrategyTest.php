<?php namespace strategies;

use app\models\payonline\Partner;
use app\models\payonline\User;
use app\models\payonline\Uslugatovar;
use app\services\payment\banks\BankAdapterBuilder;
use app\services\payment\forms\CardRegForm;
use app\services\payment\models\PaySchet;
use Yii;

class MfoCardRegStrategyTest extends \Codeception\Test\Unit
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
    public function testGetDuplicateRequest()
    {
//        /** @var PaySchet $paySchet */
//        $paySchet = PaySchet::find()
//            ->where([
//                'IdOrg' => Yii::$app->params['testParams']['mfoPartnerId'],
//                'IdUsluga' => Uslugatovar::REG_CARD_ID,
//            ])
//            ->andWhere('Extid IS NOT NULL')
//            ->orderBy('ID DESC')
//            ->one();
//
//        $form = new CardRegForm();
//        $form->type = CardRegForm::CARD_REG_TYPE_BY_PAY;
//        $form->partner = Partner::findOne([
//            'ID' => Yii::$app->params['testParams']['mfoPartnerId'],
//        ]);
//        $form->extid = $paySchet->Extid;
//
//        $mfoCardRegStrategy = new MfoCardRegStrategy($form);
//        $this->assertNotNull($this->invokeMethod($mfoCardRegStrategy, 'getDuplicateRequest'));
//
//        $form->extid = '';
//        $this->assertNull($this->invokeMethod($mfoCardRegStrategy, 'getDuplicateRequest'));
    }

    public function testGetUslugatovar()
    {
//        $mfoCardRegStrategy = $this->createBaseStrategy();
//        $uslugatovar = $this->invokeMethod($mfoCardRegStrategy, 'getUslugatovar');
//        $this->assertTrue($uslugatovar->ID == Uslugatovar::REG_CARD_ID);
    }

    public function testCreateUser()
    {
//        $mfoCardRegStrategy = $this->createBaseStrategy();
//
//        $user = $this->invokeMethod($mfoCardRegStrategy, 'createUser');
//        $this->assertInstanceOf(User::class, $user);
    }

    public function testCreatePaySchet()
    {
//        $uslugatovar = Uslugatovar::findOne(['ID' => Uslugatovar::REG_CARD_ID]);
//        $partner = Partner::findOne([
//            'ID' => Yii::$app->params['testParams']['mfoPartnerId'],
//        ]);
//
//        $form = new CardRegForm();
//        $form->type = CardRegForm::CARD_REG_TYPE_BY_PAY;
//        $form->partner = Partner::findOne([
//            'ID' => Yii::$app->params['testParams']['mfoPartnerId'],
//        ]);
//        $form->extid = '';
//        $form->successurl = 'http://example.ru/1';
//        $form->failurl = 'http://example.ru/2';
//        $form->cancelurl = 'http://example.ru/3';
//        $form->postbackurl = 'http://example.ru/4';
//        $form->postbackurl_v2 = 'http://example.ru/5';
//
//        $mfoCardRegByPayStrategy = new MfoCardRegStrategy($form);
//
//        $bankAdapterBuilder = new BankAdapterBuilder();
//        $bankAdapterBuilder->build($partner, $uslugatovar);
//
//        /** @var PaySchet $paySchet */
//        $paySchet = $this->invokeMethod(
//            $mfoCardRegByPayStrategy,
//            'createPaySchet',
//            [$bankAdapterBuilder]
//        );
//
//        $this->assertTrue($paySchet->IdUsluga == 1);
//        $this->assertTrue($paySchet->IdOrder == 0);
//        $this->assertTrue($paySchet->IdOrg == $partner->ID);
//        $this->assertTrue($paySchet->Extid == '');
//        $this->assertTrue($paySchet->Status == 0);
//        $this->assertTrue($paySchet->ErrorInfo == '');
//        $this->assertTrue($paySchet->Bank == $bankAdapterBuilder->getBankAdapter()->getBankId());
//
//        $this->assertTrue($paySchet->SuccessUrl == 'http://example.ru/1');
//        $this->assertTrue($paySchet->FailedUrl == 'http://example.ru/2');
//        $this->assertTrue($paySchet->CancelUrl == 'http://example.ru/3');
//        $this->assertTrue($paySchet->PostbackUrl == 'http://example.ru/4');
//        $this->assertTrue($paySchet->PostbackUrl_v2 == 'http://example.ru/5');
//
//        $this->assertTrue($paySchet->getFromUrl() == Yii::$app->params['domain'] . '/pay/form/' . $paySchet->ID);
    }

    public function testCreatePaySchetByOut()
    {
//        $uslugatovar = Uslugatovar::findOne(['ID' => Uslugatovar::REG_CARD_ID]);
//        $partner = Partner::findOne([
//            'ID' => Yii::$app->params['testParams']['mfoPartnerId'],
//        ]);
//
//        $form = new CardRegForm();
//        $form->type = CardRegForm::CARD_REG_TYPE_BY_OUT;
//        $form->partner = Partner::findOne([
//            'ID' => Yii::$app->params['testParams']['mfoPartnerId'],
//        ]);
//        $form->extid = (string)random_int(1000000, 100000000);
//        $form->successurl = 'http://example.ru/1';
//        $form->failurl = 'http://example.ru/2';
//        $form->cancelurl = 'http://example.ru/3';
//        $form->postbackurl = 'http://example.ru/4';
//        $form->postbackurl_v2 = 'http://example.ru/5';
//
//        $mfoCardRegByPayStrategy = new MfoCardRegStrategy($form);
//
//        $bankAdapterBuilder = new BankAdapterBuilder();
//        $bankAdapterBuilder->build($partner, $uslugatovar);
//
//        /** @var PaySchet $paySchet */
//        $paySchet = $this->invokeMethod(
//            $mfoCardRegByPayStrategy,
//            'createPaySchet',
//            [$bankAdapterBuilder]
//        );
//
//        $this->assertTrue($paySchet->IdUsluga == 1);
//        $this->assertTrue($paySchet->IdOrder == 0);
//        $this->assertTrue($paySchet->IdOrg == $partner->ID);
//        $this->assertTrue($paySchet->Extid == $form->extid);
//        $this->assertTrue($paySchet->Status == 0);
//        $this->assertTrue($paySchet->ErrorInfo == '');
//        $this->assertTrue($paySchet->Bank == 0);
//
//        $this->assertTrue($paySchet->SuccessUrl == 'http://example.ru/1');
//        $this->assertTrue($paySchet->FailedUrl == 'http://example.ru/2');
//        $this->assertTrue($paySchet->CancelUrl == 'http://example.ru/3');
//        $this->assertTrue($paySchet->PostbackUrl == 'http://example.ru/4');
//        $this->assertTrue($paySchet->PostbackUrl_v2 == 'http://example.ru/5');
//
//        $this->assertTrue($paySchet->getFromUrl() == Yii::$app->params['domain'] . '/mfo/default/outrcard' . $paySchet->ID);
    }

    private function createBaseStrategy()
    {
//        $form = new CardRegForm();
//        $form->partner = Partner::findOne([
//            'ID' => Yii::$app->params['testParams']['mfoPartnerId'],
//        ]);
//        $form->extid = '';
//        $form->type = CardRegForm::CARD_REG_TYPE_BY_PAY;
//        return new MfoCardRegStrategy($form);
    }


    /**
     * Call protected/private method of a class.
     *
     * @param object &$object    Instantiated object that we will run method on.
     * @param string $methodName Method name to call
     * @param array  $parameters Array of parameters to pass into method.
     *
     * @return mixed Method return.
     */
    private function invokeMethod(&$object, $methodName, array $parameters = array())
    {
//        $reflection = new \ReflectionClass(get_class($object));
//        $method = $reflection->getMethod($methodName);
//        $method->setAccessible(true);
//
//        return $method->invokeArgs($object, $parameters);
    }
}
