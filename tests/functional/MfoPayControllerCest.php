<?php 

class MfoPayControllerCest
{
    public function _before(FunctionalTester $I)
    {
    }

    public function tryTolkTest(FunctionalTester $I)
    {
        $I->haveHttpHeader('X-Mfo', 117);
        $I->haveHttpHeader('X-Token', '085c939f7161f9aee0d649c93062e0740d6af744');
        $I->amOnRoute('mfo/pay/lk');
        $I->see('{"status":0,"message":null}');
    }

    public function tryToFormLkTest(FunctionalTester $I)
    {
        $I->haveHttpHeader('X-Mfo', 117);
        $I->haveHttpHeader('X-Token', '085c939f7161f9aee0d649c93062e0740d6af744');
        $I->amOnRoute('mfo/pay/form-lk');
        $I->see('{"status":0,"message":"Amount must be no less than 1."}');
    }

    public function tryToAutoTest(FunctionalTester $I)
    {
        $I->haveHttpHeader('X-Mfo', 117);
        $I->haveHttpHeader('X-Token', '085c939f7161f9aee0d649c93062e0740d6af744');
        $I->amOnRoute('mfo/pay/auto');
        $I->see('{"status":0,"message":"Нет такой карты"}');
    }

    public function tryToFormAutoPartsTest(FunctionalTester $I)
    {
        $I->haveHttpHeader('X-Mfo', 117);
        $I->haveHttpHeader('X-Token', '085c939f7161f9aee0d649c93062e0740d6af744');
        $I->amOnRoute('mfo/pay/auto-parts');
        $I->see('{"status":0,"message":"Не указана карта"}');
    }

    public function tryToStatePartsTest(FunctionalTester $I)
    {
        $I->haveHttpHeader('X-Mfo', 117);
        $I->haveHttpHeader('X-Token', '085c939f7161f9aee0d649c93062e0740d6af744');
        $I->amOnRoute('mfo/pay/state');
        $I->see('{"status":0,"message":"Счет не найден"}');
    }
}