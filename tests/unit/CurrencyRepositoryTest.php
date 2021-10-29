<?php

use app\services\payment\models\Currency;
use app\services\payment\models\repositories\CurrencyRepository;

class CurrencyRepositoryTest extends \Codeception\Test\Unit
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
    public function testGetAll()
    {
        $this->assertEquals(Currency::find()->all(), CurrencyRepository::getAll());
    }

    public function testGetCurrency()
    {
        $repository = new CurrencyRepository();
        $this->assertEquals(Currency::find()->all(), $repository->getCurrency());
        $this->assertEquals(Currency::findOne(['Id' => '1']), $repository->getCurrency(null, 1));
        $this->assertEquals(Currency::findOne(['Code' => 'RUB']), $repository->getCurrency('RUB'));
        $this->assertEquals(Currency::findOne(['Code' => 'RUB']), $repository->getCurrency('RUB', 1));
        $this->assertEquals(null, $repository->getCurrency('RUB', 2));
        $this->assertEquals(null, $repository->getCurrency('rub'));
        $this->assertEquals(Currency::find()->all(), $repository->getCurrency(null, null));
    }
}