<?php

use app\models\crypt\Crypt;

class ModelsCryptCryptTest extends \Codeception\Test\Unit
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

    public function testEncryptDecrypt()
    {
        $crypt = new Crypt();
        $ciphertext = $crypt->encrypt('test', 'test');
        $this->assertEquals('test', $crypt->decrypt($ciphertext, 'test'));
    }
}