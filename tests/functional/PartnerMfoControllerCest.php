<?php

use app\services\balance\models\PartsBalancePartnerForm;

class PartnerMfoControllerCest
{
    public function _before(FunctionalTester $I)
    {
        $I->amLoggedInAs(app\models\partner\UserLk::findIdentity('admin'));
    }

    public function tryToPartsBalanceTest(FunctionalTester $I)
    {
        $I->amOnRoute('partner/mfo/parts-balance');
        $I->see('Пользователь: Администратор');
        $I->see('Баланс по разбивке (Платформа)');
    }

    public function tryToPartsBalanceProcessingTest(FunctionalTester $I)
    {

    }

    public function tryToPartsBalancePartnerTest(FunctionalTester $I)
    {
        $I->amOnRoute('partner/mfo/parts-balance-partner');
        $I->see('Пользователь: Администратор');
        $I->see('Баланс по разбивке (Партнер)');
    }

    public function tryToPartsBalancePartnerProcessingTest(FunctionalTester $I)
    {

    }

    public function tryToBalanceorderTest(FunctionalTester $I)
    {

    }

    public function tryToExportvypTest(FunctionalTester $I)
    {
        $I->amOnRoute('partner/mfo/exportvyp', ['idpartner' => 117]);
        $I->see('{');
    }
}
