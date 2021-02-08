<?php 

class PartnerMfoControllerCest
{
    public function _before(FunctionalTester $I)
    {
    }

    // tests
    public function tryToPartsBalanceTest(FunctionalTester $I)
    {
        $I->amOnPage('/partner/mfo/parts-balance');
        $I->see('Сервис VEPAY');
    }
}
