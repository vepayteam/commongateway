<?php

use app\models\bank\TcbGate;

class ModelsBankTcbGateTest extends \Codeception\Test\Unit
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
        $tcbGate = new TcbGate(0,0);
        $result = [
            13 => 7,
            10 => 2,
            12 => 2,
            11 => 1,
            14 => 3,
            2 => 3,
            17 => 4,
            19 => 4,
            0 => 0,
            1 => 5,
            16 => 5,
            21 => 6,
            23 => 6,
            102 => 100,
            100 => 100,
            114 => 100,
            112 => 100,
            110 => 100,
            116 => 100
        ];
        $this->tester->assertEquals($result, $tcbGate->GetIsCustomBankGates());
    }

    public function testSetTypeGate()
    {
        $tcbGate = new TcbGate(0,0);
        $tcbGate->SetTypeGate(13);
        $this->tester->assertEquals(7, $tcbGate->typeGate);
        $tcbGate->SetTypeGate(10);
        $this->tester->assertEquals(2, $tcbGate->typeGate);
        $tcbGate->SetTypeGate(12);
        $this->tester->assertEquals(2, $tcbGate->typeGate);
        $tcbGate->SetTypeGate(11);
        $this->tester->assertEquals(1, $tcbGate->typeGate);
        $tcbGate->SetTypeGate(14);
        $this->tester->assertEquals(3, $tcbGate->typeGate);
        $tcbGate->SetTypeGate(2);
        $this->tester->assertEquals(3, $tcbGate->typeGate);
        $tcbGate->SetTypeGate(17);
        $this->tester->assertEquals(4, $tcbGate->typeGate);
        $tcbGate->SetTypeGate(19);
        $this->tester->assertEquals(4, $tcbGate->typeGate);
        $tcbGate->SetTypeGate(0);
        $this->tester->assertEquals(0, $tcbGate->typeGate);
        $tcbGate->SetTypeGate(1);
        $this->tester->assertEquals(5, $tcbGate->typeGate);
        $tcbGate->SetTypeGate(16);
        $this->tester->assertEquals(5, $tcbGate->typeGate);
        $tcbGate->SetTypeGate(21);
        $this->tester->assertEquals(6, $tcbGate->typeGate);
        $tcbGate->SetTypeGate(23);
        $this->tester->assertEquals(6, $tcbGate->typeGate);
        $tcbGate->SetTypeGate(102);
        $this->tester->assertEquals(100, $tcbGate->typeGate);
        $tcbGate->SetTypeGate(100);
        $this->tester->assertEquals(100, $tcbGate->typeGate);
        $tcbGate->SetTypeGate(114);
        $this->tester->assertEquals(100, $tcbGate->typeGate);
        $tcbGate->SetTypeGate(112);
        $this->tester->assertEquals(100, $tcbGate->typeGate);
        $tcbGate->SetTypeGate(110);
        $this->tester->assertEquals(100, $tcbGate->typeGate);
        $tcbGate->SetTypeGate(116);
        $this->tester->assertEquals(100, $tcbGate->typeGate);
    }

    public function testGetGates()
    {
        $tcbGate = new TcbGate(117,0);
        $res = $tcbGate->GetGates();
        $this->tester->assertEquals('T1653100822ID', $res['LoginTkbAft']);
    }

    public function testIsGate()
    {
        $tcbGate = new TcbGate(117,7);
        $this->tester->assertEquals(true, $tcbGate->IsGate());
    }

    public function testGetTypeGate()
    {
        $tcbGate = new TcbGate(117,7);
        $this->tester->assertEquals(7, $tcbGate->getTypeGate());
    }

    public function testGetBank()
    {
        $tcbGate = new TcbGate(117,7);
        $this->tester->assertEquals(2, $tcbGate->getBank());
    }
}