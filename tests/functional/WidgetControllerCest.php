<?php

class WidgetControllerCest
{
    public function _before(FunctionalTester $I)
    {

    }

    public function tryToOrderTest(FunctionalTester $I)
    {
        $I->amOnRoute('widget/order', ['id' => 64]);
        $I->see('Сумма к оплате');
    }

    public function tryToPayTest(FunctionalTester $I)
    {
        $I->amOnRoute('widget/pay', ['prov' => 114, 'sum' => 1]);
        $I->see('Сумма к оплате 0,01 ₽ Адрес магазина Тест магазин Номер карты Действует Владелец CVC Почта для отправления чека Оплатить');
    }

    public function tryToOrderdoneTest(FunctionalTester $I)
    {
        $I->amOnRoute('widget/orderdone', ['id' => 1]);
        $I->see('VEPAY - VEPAY v 1.0.7 Сервис VEPAY');
    }

    public function tryToOrderokTest(FunctionalTester $I)
    {
        Yii::$app->session->set('IdWidgetPay', 1);
        $I->amOnRoute('widget/orderok', ['id' => 1]);
        $I->see('Платеж находится в обработке');
    }
}
