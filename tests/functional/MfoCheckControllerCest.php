<?php 

class MfoCheckControllerCest
{
    public function _before(FunctionalTester $I)
    {
    }

    public function tryToIndexTest(FunctionalTester $I)
    {
        $I->haveHttpHeader('X-Mfo', 117);
        $I->haveHttpHeader('X-Token', '085c939f7161f9aee0d649c93062e0740d6af744');
        $I->amOnRoute('mfo/check');
        $I->see('{"status":1,"message":""}');
    }
}
