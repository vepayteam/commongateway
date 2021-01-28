<?php


namespace app\commands;

use app\models\payonline\Partner;
use yii\console\Controller;
use app\services\cards\DeleteOldPan;
use app\services\payment\banks\TKBankAdapter;

/**
 * Class SyncBalanceViaBankApiController
 * @package app\commands
 */
class SyncBalanceViaBankApiController extends Controller
{
    
    public function actionIndex(): void
    {
        $partner_ids = [
            8, //ООО "МКК ВАН КЛИК МАНИ"
        ];
        /* @var $partners Partner[] */
        $partners = Partner::find()->where(['ID' => $partner_ids])->all();
        foreach ($partners as $partner) {
            $bank = new TKBankAdapter;

            $gate = $partner->getBankGates()->where([
                'Enable' => 1
            ])->orderBy('Priority DESC')->one();
            $bank->setGate($gate);
            // 'SchetTcb' => 'Номер транзитного счета ТКБ на выдачу',
            $amount =
                isset($bank->getBalanceAcc(['account' => $partner->SchetTcb])['amount']) ?
                $bank->getBalanceAcc(['account' => $partner->SchetTcb])['amount'] : 0;
            $partner->BalanceOut = $amount;
            $partner->save(false);
        }
    }
}

