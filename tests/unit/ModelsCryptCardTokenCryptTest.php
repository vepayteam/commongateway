<?php

use app\models\crypt\Tokenizer;
use app\models\crypt\CardToken;

class ModelsCryptCardTokenCryptTest extends \Codeception\Test\Unit
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
        $cardToken = new CardToken();
        $tokenizer = new Tokenizer();
        $cardToken->CreateToken('5555555555555555', '1224', 'Test Testov');
        $panToken = Yii::$app->db->createCommand('SELECT `CryptoKeyId`, `EncryptedPAN` FROM `pan_token` ORDER BY id DESC LIMIT 1')->queryOne();
        $tokenizerReflectionClass = new ReflectionClass(Tokenizer::class);
        $decrypt = $tokenizerReflectionClass->getMethod('Decrypt');
        $decrypt->setAccessible(true);
        $this->assertEquals('5555555555555555', $decrypt->invoke($tokenizer, $panToken['EncryptedPAN'], $panToken['CryptoKeyId']));
    }

    public function testGetCardByToken()
    {
        $cardToken = new CardToken();
        $token = $cardToken->CreateToken('5555555555555555', '1224', 'Test Testov');
        $this->assertEquals('5555555555555555', $cardToken->GetCardByToken($token));
    }

    public function testCheckExistToken()
    {
        $cardToken = new CardToken();
        $token = $cardToken->CreateToken('5555555555555555', '1224', 'Test Testov');
        $this->assertEquals($token, $cardToken->CheckExistToken('5555555555555555', '1224'));
    }
}