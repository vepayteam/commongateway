<?php 

class LkPartnerCest
{
    public function _before(FunctionalTester $I)
    {
    }

    // tests
    public function tryToWork(FunctionalTester $I)
    {
        $I->amOnPage('/');
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
    }
}
