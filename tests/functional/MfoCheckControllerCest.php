<?php 

class MfoCheckControllerCest
{
    public function _before(FunctionalTester $I)
    {
    }

    public function tryToIndexTest(FunctionalTester $I)
    {
        $I->haveHttpHeader('X-Mfo', 117);
        $I->haveHttpHeader('X-Token', 117);
        $I->amOnRoute('mfo/check');
        $I->see('{"status":1}');
    }
}
