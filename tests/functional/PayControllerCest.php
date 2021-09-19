<?php

class PayControllerCest
{
    public function _before(FunctionalTester $I)
    {
    }

    public function tryToFormDataTest(FunctionalTester $I)
    {
        $I->amOnRoute('pay/form-data', ['id' => 0]);
        $I->see('Not Found: Счет для оплаты не найден');
    }

    public function tryToSaveDataTest(FunctionalTester $I)
    {
        $I->amOnRoute('pay/save-data', ['id' => 0]);
        $I->see('VEPAY - VEPAY v 1.0.7 Сервис VEPAY.');
        $I->seeCurrentUrlEquals('/pay/form/0');
    }

    public function tryToFormTest(FunctionalTester $I)
    {
        $I->amOnRoute('pay/form', ['id' => 0]);
        $I->see('Not Found: Счет для оплаты не найден');
    }

    public function tryToCreatepayTest(FunctionalTester $I)
    {
        $I->amOnRoute('pay/createpay');
        $I->see(' Not Found:');
    }

    public function tryToDeclinepayTest(FunctionalTester $I)
    {
        $I->amOnRoute('pay/declinepay');
        $I->see(' Not Found:');
    }

    public function tryToOrderdoneTest(FunctionalTester $I)
    {
        $I->amOnRoute('pay/orderdone', ['id' => 0]);
        $I->see(' Bad Request:');
    }

    public function tryToOrderokTest(FunctionalTester $I)
    {
        $I->amOnRoute('pay/orderok', ['id' => 0]);
        $I->see(' Not Found:');
    }
}
