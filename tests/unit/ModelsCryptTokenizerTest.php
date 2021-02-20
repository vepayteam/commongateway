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
        $this->assertEquals(['kek1' => 1, 'kek2' => 1, 'kek3' => 1, 'crypt' => 0, 'count' => '1300', 'countwork' => '1300'], $tokenizer->TestKek());
    }

    public function testCheckExistToken()
    {
        $tokenizer = new Tokenizer();
        $token = $tokenizer->CreateToken('5555555555555555', '1224', 'Test Testov');
        $this->assertEquals($token, $tokenizer->CheckExistToken('5555555555555555', '1224'));
    }
}