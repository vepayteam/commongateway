<?php 

class MfoOutControllerCest
{
    public function _before(FunctionalTester $I)
    {
    }

    public function tryToPaycardTest(FunctionalTester $I)
    {
        $I->haveHttpHeader('X-Mfo', 117);
        $I->haveHttpHeader('X-Token', 117);
        $I->amOnRoute('mfo/out/paycard');
        $I->see('{"status":0,"message":"Cardnum cannot be blank."}');
    }

    public function tryToPayaccTest(FunctionalTester $I)
    {
        $I->haveHttpHeader('X-Mfo', 117);
        $I->haveHttpHeader('X-Token', 117);
        $I->amOnRoute('mfo/out/payacc');
        $I->see('{"status":0,"message":"Fio cannot be blank."}');
    }

    public function tryToPayulTest(FunctionalTester $I)
    {
        $I->haveHttpHeader('X-Mfo', 117);
        $I->haveHttpHeader('X-Token', 117);
        $I->amOnRoute('mfo/out/payul');
        $I->see('{"status":0,"message":"Name cannot be blank."}');
    }

    public function tryToStateTest(FunctionalTester $I)
    {
        $I->haveHttpHeader('X-Mfo', 117);
        $I->haveHttpHeader('X-Token', 117);
        $I->amOnRoute('mfo/out/state');
        $I->see('{"status":0,"message":"Счет не найден"}');
    }
}
