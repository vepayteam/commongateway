<?php 

class MfoCheckControllerCest
{
    public function _before(FunctionalTester $I)
    {
    }

    public function tryToIndexTest(FunctionalTester $I)
    {
        $I->haveHttpHeader('X-Mfo', 117);
        $I->haveHttpHeader('X-Token', '4db602f436fda086d9b946267dcf0959197779cb');
        $I->amOnRoute('mfo/check');
        $I->see('{"status":1}');
    }
}
