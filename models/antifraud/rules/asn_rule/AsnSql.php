<?php


namespace app\models\antifraud\rules\asn_rule;


use app\models\antifraud\rules\interfaces\ISqlRule;
use app\models\antifraud\support_objects\TransInfo;
use app\models\antifraud\tables\AFAsn;
use Yii;
use yii\db\Query;

class AsnSql implements ISqlRule
{
    private $asn;

    public function __construct(string $asn) {
        $this->asn = $asn;
    }

    /** Возвращает sql Если правилу необходимо сделать отдельный запрос*/
    public function separate_sql(): Query
    {
        $record = AFAsn::find()->where(['asn'=>$this->asn])->asArray(true);
        return $record;
    }

    /**Возвращает sql Если запрос необходимо дополнить*/
    public function compound_sql(Query $query): Query
    {
        return $query;
    }

    /**Обновляет статистику правила при удачной транзакции*/
    public function update_success_stat($trans_info): void
    {
        /*$command = Yii::$app->db->createCommand('
            UPDATE 
                antifraud_asn asn 
            SET asn.num_fails = asn.num_fails - 1 
            where asn.num_fails >= 1 
              and asn.asn = :asn
        ');
        $command->bindValues([':asn' => $this->asn]);
        $command->execute();*/
    }

    /**Обновляет статистику правила при неудачной транзакции*/
    public function update_failed_stat($trans_info): void
    {
        /*$command = Yii::$app->db->createCommand('
            UPDATE 
                antifraud_asn asn 
            SET asn.num_fails = asn.num_fails + 1
            where asn.asn = :asn
        ');
        $command->bindValues([':asn' => $this->asn]);
        $command->execute();*/
    }
}