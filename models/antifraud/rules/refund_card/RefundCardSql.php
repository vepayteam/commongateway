<?php


namespace app\models\antifraud\rules\refund_card;


use app\models\antifraud\rules\interfaces\ISqlRule;
use app\models\antifraud\support_objects\RefundInfo;
use app\models\antifraud\support_objects\TransInfo;
use app\models\TU;
use yii\db\Query;
use yii\helpers\VarDumper;
use function GuzzleHttp\Promise\all;

/**
 * @property RefundInfo $ref_info
 */
class RefundCardSql implements ISqlRule
{

    private $ref_info;

    public function __construct($ref_info)
    {
        $this->ref_info = $ref_info;
    }

    /** Возвращает sql Если правилу необходимо сделать отдельный запрос*/
    public function separate_sql(): Query
    {
        //проверить
        $partner_id = $this->ref_info->partner_id();
        $card_mask = $this->ref_info->card_mask();
        $ext_id = $this->ref_info->ext_id(); //значения до точки.
        $sum = $this->ref_info->sum();
        $transaction_id = $this->ref_info->transaction_id();

        $query = new Query();
        $query
            ->select('pay_schet.ID, IdOrg, Status, Extid, CardNum, SummPay, DateOplat, DateCreate, TimeElapsed')
            ->from('pay_schet')
            ->where(['IdOrg' => $partner_id, 'Status' => [0, 1], 'CardNum' => $card_mask, 'SummPay'=> $sum])
            ->andWhere(['>', 'pay_schet.DateCreate', strtotime('yesterday')])
            ->andWhere(['<>','pay_schet.ID', $transaction_id])
            ->leftJoin('uslugatovar', 'IdUsluga = uslugatovar.ID')
            ->andWhere(['uslugatovar.IsCustom' => TU::$TOCARD]);

        if (!empty($ext_id)) {
            $query->andWhere(['pay_schet.Extid' => $ext_id]);
            //$query->andWhere(['like', 'Extid', $ext_id]);
        }
        return $query;
    }

    /**Возвращает sql Если запрос необходимо дополнить*/
    public function compound_sql(Query $query): Query
    {
        return $query;
    }

    /**Обновляет статистику правила при удачной транзакции*/
    public function update_success_stat($trans_info): void
    {

    }

    /**Обновляет статистику правила при неудачной транзакции*/
    public function update_failed_stat($trans_info): void
    {

    }
}