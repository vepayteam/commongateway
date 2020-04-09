<?php


namespace app\models\partner\stat;

use app\models\bank\TCBank;
use app\models\payonline\Partner;
use Yii;
use yii\base\BaseObject;

class VyvodInfo extends BaseObject
{
    /* @var Partner $Partner */
    public $Partner;
    public $DateFrom;
    public $DateTo;

    public function __construct($config = [])
    {
        parent::__construct($config);
    }

    /**
     * Сумма перечислений по выписке счета погашения
     * @param $Type
     * @return int
     * @throws \yii\db\Exception
     */
    public function GetSummPepechislen($Type)
    {
        $sum = 0;
        $res = Yii::$app->db->createCommand("
            SELECT 
                `SummPP`,
                `Description`,
                `Bic`
            FROM
                `statements_account`
            WHERE
                `IdPartner` = :IDPARTNER
                AND `TypeAccount` = 1
                AND `DatePP` BETWEEN :DATEFOM AND :DATETO
                AND `IsCredit` = 0
        ", [
            ':IDPARTNER' => $this->Partner->ID,
            ':DATEFOM' => $this->DateFrom,
            ':DATETO' => $this->DateTo
        ])->query();

        while ($row = $res->read()) {
            if ($this->IsVyvodNazn($row['Description'])) {
                if ($Type == 0 && $row['Bic'] == TCBank::BIC) {
                    //перечисление на выдачу
                    $sum += $row['SummPP'];
                } elseif ($Type == 1 && $row['Bic'] != TCBank::BIC) {
                    //перечисление на р/с
                    $sum += $row['SummPP'];
                }
            }
        }

        return $sum;
    }

    private function IsVyvodNazn($descript)
    {
        return (
                mb_stripos($descript, 'Перевод средств между своими счетами') !== false ||
                mb_stripos($descript, 'Перевод денежных средств между счетами Клиента') !== false ||
                mb_stripos($descript, 'Сальдирование парных счетов') !== false ||
                mb_stripos($descript, 'Расчеты по договору') !== false
            ) &&
            $this->DevideDS($descript);
    }

    private function DevideDS($descript)
    {
        if ($this->Partner->ID == 10) {
            return mb_stripos($descript, '(CTO)') !== false;
        }

        if ($this->Partner->ID == 4) {
            return mb_stripos($descript, '(CTO)') === false;
        }

        return 1;
    }

    public function SumPostuplen()
    {
        $sum = 0;
        $res = Yii::$app->db->createCommand("
            SELECT 
                `SummPP`,
                `Inn`                   
            FROM
                `statements_account`
            WHERE
                `IdPartner` = :IDPARTNER
                AND `TypeAccount` = 0
                AND `DatePP` BETWEEN :DATEFOM AND :DATETO
                AND `IsCredit` = 1
        ", [
            ':IDPARTNER' => $this->Partner->ID,
            ':DATEFOM' => $this->DateFrom,
            ':DATETO' => $this->DateTo
        ])->query();

        while ($row = $res->read()) {
            if ($this->Partner->INN == $row['Inn']) {
                $sum += $row['SummPP'];
            }
        }

        return $sum;
    }
}