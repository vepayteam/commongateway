<?php 

class MfoCardControllerCest
{
    public function _before(FunctionalTester $I)
    {
    }

    public function tryToInfoTest(FunctionalTester $I)
    {
        $I->haveHttpHeader('X-Mfo', 117);
        $I->haveHttpHeader('X-Token', '085c939f7161f9aee0d649c93062e0740d6af744');
        $I->amOnRoute('mfo/card/info');
        $I->see('{"status":0,"message":"Нет такой карты"}');
    }

    public function tryToRegTest(FunctionalTester $I)
    {
        $I->haveHttpHeader('X-Mfo', 117);
        $I->haveHttpHeader('X-Token', '085c939f7161f9aee0d649c93062e0740d6af744');
        $I->amOnRoute('mfo/card/info');
        $I->see('{"status":0,"message":"Нет такой карты"}');
    }

    public function tryToGetTest(FunctionalTester $I)
    {
        $I->haveHttpHeader('X-Mfo', 117);
        $I->haveHttpHeader('X-Token', '085c939f7161f9aee0d649c93062e0740d6af744');
        $I->amOnRoute('mfo/card/info');
        $I->see('{"status":0,"message":"Нет такой карты"}');
    }

    public function tryToDelTest(FunctionalTester $I)
    {
        $I->haveHttpHeader('X-Mfo', 117);
        $I->haveHttpHeader('X-Token', '085c939f7161f9aee0d649c93062e0740d6af744');
        $I->amOnRoute('mfo/card/info');
        $I->see('{"status":0,"message":"Нет такой карты"}');
    }
}
