<?php 

class WidgetControllerCest
{
    public function _before(FunctionalTester $I)
    {
    }

    // tests
    public function tryToTest(FunctionalTester $I)
    {
        $I->amOnPage('/widget/order/64');
//        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::NOT_FOUND);
        $I->see('Сервис VEPAY');
    }
}
