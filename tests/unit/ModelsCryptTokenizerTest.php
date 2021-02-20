<?php

use app\models\crypt\Tokenizer;

class ModelsCryptTokenizerTest extends \Codeception\Test\Unit
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

    public function testCreateToken()
    {
//        $tokenizer = new Tokenizer();
//        $tokenizer->CreateToken('5555555555555555', '1224', 'Test Testov');
//        $panToken = Yii::$app->db->createCommand('SELECT `CryptoKeyId`, `EncryptedPAN` FROM `pan_token` ORDER BY id DESC LIMIT 1')->queryOne();
//        $tokenizerReflectionClass = new ReflectionClass(Tokenizer::class);
//        $decrypt = $tokenizerReflectionClass->getMethod('Decrypt');
//        $decrypt->setAccessible(true);
//        $this->assertEquals('5555555555555555', $decrypt->invoke($tokenizer, $panToken['EncryptedPAN'], $panToken['CryptoKeyId']));
    }

    public function testGetKek()
    {
//        $tokenizer = new Tokenizer();
//        $this->assertEquals(30, strlen($tokenizer->GetKek()));
    }

    public function testTestKek()
    {
        $tokenizer = new Tokenizer();
        $result = $tokenizer->TestKek();
        $this->tester->assertTrue(isset($result['kek1']));
        $this->tester->assertTrue(isset($result['kek2']));
        $this->tester->assertTrue(isset($result['kek3']));
        $this->tester->assertTrue(isset($result['crypt']));
        $this->tester->assertTrue(is_numeric($result['count']));
        $this->tester->assertTrue(is_numeric($result['countwork']));
    }

    public function testCheckExistToken()
    {
        $tokenizer = new Tokenizer();
        $token = $tokenizer->CreateToken('5555555555555555', '1224', 'Test Testov');
        $this->assertEquals($token, $tokenizer->CheckExistToken('5555555555555555', '1224'));
    }
}