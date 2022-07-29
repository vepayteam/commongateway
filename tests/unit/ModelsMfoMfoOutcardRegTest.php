<?php

use app\models\mfo\MfoOutcardReg;
use app\models\payonline\User;

class ModelsMfoMfoOutcardRegTest extends \Codeception\Test\Unit
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

    public function testMaskedCardNumber()
    {
        $mfoOutcardReg = new MfoOutcardReg();
        $this->tester->assertEquals('555555******5555', $mfoOutcardReg->MaskedCardNumber('555555555555555'));
    }

    public function testSaveOutard()
    {
        $mfoOutcardReg = new MfoOutcardReg();
        $user = new User();
        $this->tester->assertEquals(0, $mfoOutcardReg->SaveCard(0, 0,0, $user, 0));
    }

    public function testSaveCard()
    {
        $mfoOutcardReg = new MfoOutcardReg();
        $user = new User();
        $user->ID = 0;
        $mfoOutcardRegReflectionClass = new ReflectionClass(MfoOutcardReg::class);
        $saveOutard = $mfoOutcardRegReflectionClass->getMethod('SaveOutard');
        $saveOutard->setAccessible(true);
        $this->tester->assertIsNumeric($saveOutard->invoke($mfoOutcardReg, null, null, $user));
    }
}