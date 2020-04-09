<?php


namespace app\models\crypt;


use yii\base\Model;

class LogKeyUser extends Model
{
    public $datefrom;
    public $dateto;

    public function rules()
    {
        return [
            [['datefrom', 'dateto'], 'date', 'format' => 'php:d.m.Y']
        ];
    }

    public function GetList()
    {
        $res = \Yii::$app->db->createCommand("
            SELECT 
              l.`ID`,
              l.`Date`,
              l.`IdUser`,
              u.Login,
              l.`Type`,
              l.`IPLogin`,
              l.`DopInfo` 
            FROM
              `key_log` AS l
              LEFT JOIN `key_users` AS u ON u.ID = l.IdUser
            WHERE
               l.`Date` BETWEEN :DATEFROM AND :DATETO
            ORDER BY `ID` DESC
        ", [':DATEFROM' => strtotime($this->datefrom), ':DATETO' => strtotime($this->dateto)+86400])->query();

        $ret = [];
        while ($row = $res->read()) {
            $ret[] = $row;
        }

        return $ret;
    }
}