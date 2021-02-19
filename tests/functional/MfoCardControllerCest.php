<?php 

class MfoCardControllerCest
{
    public function _before(FunctionalTester $I)
    {
    }

    public function tryToInfoTest(FunctionalTester $I)
    {
        $I->haveHttpHeader('X-Mfo', 117);
        $I->haveHttpHeader('X-Token', '4db602f436fda086d9b946267dcf0959197779cb');
        $I->amOnRoute('mfo/card/info');
        $I->see('{"status":0,"message":"Нет такой карты"}');
    }

    public function tryToRegTest(FunctionalTester $I)
    {
        $I->haveHttpHeader('X-Mfo', 117);
        $I->haveHttpHeader('X-Token', '4db602f436fda086d9b946267dcf0959197779cb');
        $I->amOnRoute('mfo/card/info');
        $I->see('{"status":0,"message":"Нет такой карты"}');
    }

    public function tryToGetTest(FunctionalTester $I)
    {
        $I->haveHttpHeader('X-Mfo', 117);
        $I->haveHttpHeader('X-Token', '4db602f436fda086d9b946267dcf0959197779cb');
        $I->amOnRoute('mfo/card/info');
        $I->see('{"status":0,"message":"Нет такой карты"}');
    }

    public function tryToDelTest(FunctionalTester $I)
    {
        $I->haveHttpHeader('X-Mfo', 117);
        $I->haveHttpHeader('X-Token', '4db602f436fda086d9b946267dcf0959197779cb');
        $I->amOnRoute('mfo/card/info');
        $I->see('{"status":0,"message":"Нет такой карты"}');
    }
}
