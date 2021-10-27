<?php

namespace unit;

use app\services\payment\banks\FortaTechAdapter;
use app\services\payment\forms\AutoPayForm;
use Codeception\Test\Unit;
use yii\base\ErrorException;

class FortaTechTest extends Unit
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

    public function testRecurrentPay()
    {
        $autoPayForm = $this->getMockBuilder(AutoPayForm::class)->getMock();
        $fortaTechAdapter = new FortaTechAdapter();
        $fortaTechAdapterReflectionClass = new \ReflectionClass(FortaTechAdapter::class);
        $recurrentPay = $fortaTechAdapterReflectionClass->getMethod('recurrentPay');
        $recurrentPay->setAccessible(true);
        $this->expectException(ErrorException::class);
        $this->assertEquals(null, $recurrentPay->invoke($fortaTechAdapter, $autoPayForm));
    }
}
