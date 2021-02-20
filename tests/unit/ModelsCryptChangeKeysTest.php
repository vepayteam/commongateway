<?php

use app\models\crypt\ChangeKeys;

class ModelsCryptChangeKeysTest extends \Codeception\Test\Unit
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

    public function testSomeFeature()
    {
//        Не работет на тесте
//        Yii::$app->session['KeyUser'] = 3;
//        $changeKeys = new ChangeKeys();
//        $changeKeys->key1 = md5('test1');
//        $changeKeys->key2 = md5('test2');
//        $changeKeys->key3 = md5('test3');
//        $changeKeys->SaveRecryptKeys();
//        $key1 = Yii::$app->db->createCommand('SELECT `Value` FROM `keys` AS `k` WHERE `k`.ID = 10')->queryOne();
//        $key2 = Yii::$app->db->createCommand('SELECT `Value` FROM `keys` AS `k` WHERE `k`.ID = 11')->queryOne();
//        $key3 = Yii::$app->db->createCommand('SELECT `Value` FROM `keys` AS `k` WHERE `k`.ID = 12')->queryOne();
//        $this->tester->assertEquals('5a105e8b9d40e1329780d62ea2265d8a', $key1['Value']);
//        $this->tester->assertEquals('ad0234829205b9033196ba818f7a872b', $key2['Value']);
//        $this->tester->assertEquals('8ad8757baa8564dc136c1e07507f4a98', $key3['Value']);
    }

    public function testReencrypKards()
    {
        Yii::$app->session['KeyUser'] = 3;
        $changeKeys = new ChangeKeys();
        $changeKeys->key1 = md5('test1');
        $changeKeys->key2 = md5('test2');
        $changeKeys->key3 = md5('test3');
        $this->tester->assertEquals(0, $changeKeys->ReencrypKards());
    }
}