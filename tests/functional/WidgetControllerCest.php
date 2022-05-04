<?php

use app\models\payonline\Partner;
use app\services\payment\models\PaySchet;

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
        $partner = $this->getPartner();
        $successPaySchet = $this->getSuccessPaySchet($partner);

        $I->amOnRoute('widget/orderdone', ['id' => $successPaySchet->ID]);
        $I->see('VEPAY - VEPAY v 1.0.7 Сервис VEPAY');
    }

    public function tryToOrderokTest(FunctionalTester $I)
    {
        $partner = $this->getPartner();
        $successPaySchet = $this->getSuccessPaySchet($partner);

        Yii::$app->session->set('IdWidgetPay', $successPaySchet->ID);
        $I->amOnRoute('widget/orderok', ['id' => $successPaySchet->ID]);
        $I->see('Платёж прошёл успешно Средства поступили на счёт продавца, теперь вы можете вернуться в магазин Оплата прошла успешно. Вернуться в магазин');
    }

    private function getPartner(): Partner
    {
        $payschetTable = PaySchet::tableName();
        /** @var Partner $partner */
        $partner = Partner::find()
            ->leftJoin('uslugatovar', 'uslugatovar.IDPartner=partner.ID')
            ->leftJoin('partner_bank_gates', 'partner_bank_gates.PartnerId=partner.ID')
            ->leftJoin($payschetTable, "{$payschetTable}.IdOrg=partner.ID")
            ->where(['uslugatovar.IsCustom' => \app\models\TU::$ECOM, 'uslugatovar.IsDeleted' => 0])
            ->andWhere('partner_bank_gates.TU=uslugatovar.IsCustom')
            ->andWhere(['partner_bank_gates.Enable' => 1])
            ->andWhere(["{$payschetTable}.Status" => PaySchet::STATUS_DONE])
            ->one();

        return $partner;
    }

    private function getSuccessPaySchet(Partner $partner): PaySchet
    {
        /** @var PaySchet $paySchet */
        $paySchet = $partner->getPaySchets()
            ->where(['Status' => PaySchet::STATUS_DONE])
            ->one();

        return $paySchet;
    }
}
