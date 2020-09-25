<?php

namespace app\models\mfo;

use app\models\payonline\User;
use Yii;
use yii\db\Exception;

class IdentificationUser
{
    public $StateOp;
    public $ErrorMessage;
    public $Status;
    /**
     * @param $user
     * @param $extId
     * @param $IdOrg
     * @param $params
     * @return string
     * @throws Exception
     */
    public function Create($user, $IdOrg, $params)
    {
        $birth = 0;
        if (!empty($params['birth'])) {
            $birth = strtotime($params['birth']);
        }
        $paspdate = 0;
        if (!empty($params['paspdate'])) {
            $paspdate = strtotime($params['paspdate']);
        }

        if (empty($params['nam']) || empty($params['fam']) || empty($params['paspser']) || empty($params['paspnum'])) {
            return 0;
        }

        $params = [
            'IdOrg' => $IdOrg,
            'TransNum' => 0,
            'Name' => $params['nam'],
            'Fam' => $params['fam'],
            'Otch' => $params['otc'],
            'BirthDay' => $birth,
            'Inn' => strval($params['inn']),
            'Snils' => strval($params['snils']),
            'PaspSer' => strval($params['paspser']),
            'PaspNum' => strval($params['paspnum']),
            'PaspPodr' => strval($params['paspcode']),
            'PaspDate' => $paspdate,
            'PaspVidan' => strval($params['paspvid']),
            'Phone' => strval($params['phone']),
            'PhoneCode' => strval($params['phonecode']),
            'StateOp' => 0,
            'DateCreate' => time(),
        ];

        if ($user && $user->ID) {
            $params['IdUser'] = $user->ID;
        } else {
            $params['IdUser'] = 0;
        }

        Yii::$app->db->createCommand()->insert('user_identification', $params)->execute();

        return Yii::$app->db->lastInsertID;
    }

    /**
     * @param $Id
     * @param $IdOrg
     * @return int
     * @throws Exception
     */
    public function FindReq($Id, $IdOrg)
    {

        $res = Yii::$app->db->createCommand('
            SELECT
                `ID`,
                `StateOp`,
                `ErrorMessage`,
                `Status`
            FROM
                `user_identification`   
            WHERE
                `IdOrg` = :IDORG
                AND `Id` = :ID
        ', [
            ':IDORG' => $IdOrg,
            ':ID' => $Id
        ])->queryOne();

        if ($res) {
            $this->StateOp = $res['StateOp'];
            $this->ErrorMessage = $res['ErrorMessage'];
            $this->Status = $res['Status'];
            return $res['ID'];
        } else {
            return 0;
        }
    }

    public function SetTansac($id, $transac)
    {
        $q = sprintf('UPDATE `user_identification` SET `TransNum` = %s WHERE `ID` = %d', $transac, $id);
        Yii::$app->db->createCommand($q)->execute();
    }

    public function SetStatus($id, $status, $message)
    {
        $q = sprintf(
            'UPDATE `user_identification` SET `StateOp` = %d, `Status` = \'%s\' WHERE `ID` = %d',
            $status, json_encode($message), $id
        );
        Yii::$app->db->createCommand($q)->execute();
    }

}
