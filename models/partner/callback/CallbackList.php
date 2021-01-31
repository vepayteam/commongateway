<?php

namespace app\models\partner\callback;

use app\models\partner\UserLk;
use app\services\notifications\models\NotificationPay;
use Yii;
use yii\base\Model;
use yii\db\Query;

class CallbackList extends Model
{
    public $datefrom;
    public $dateto;
    public $notifstate;
    public $partner;

    public function rules()
    {
        return [
            [['partner', 'notifstate'], 'integer'],
            [['datefrom', 'dateto'], 'date', 'format' => 'php:d.m.Y'],
            [['datefrom', 'dateto'], 'required'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'datefrom' => 'Период',
            'dateto' => 'Период',
        ];
    }

    public function GetList($IsAdmin)
    {

        $idpartner = $IsAdmin ?  $this->partner : UserLk::getPartnerId(Yii::$app->user);

        $datefrom = strtotime($this->datefrom . " 00:00:00");
        $dateto = strtotime($this->dateto . " 23:59:59");
        if ($datefrom < $dateto - 365 * 86400) {
            $datefrom = $dateto - 365 * 86400;
        }

        $query = new Query();
        $query
            ->select([
                'n.ID',
                'n.IdPay',
                'n.DateCreate',
                'n.Email',
                'n.DateSend',
                'n.HttpCode',
                'n.HttpAns',
                'n.FullReq'
            ])
            ->from('`notification_pay` AS n')
            ->leftJoin('`pay_schet` AS ps', 'n.IdPay = ps.ID')
            ->where('n.DateCreate BETWEEN :DATEFROM AND :DATETO', [
                ':DATEFROM' => $datefrom,
                ':DATETO' => $dateto
            ])
            ->andWhere(['TypeNotif' => [
                NotificationPay::CRON_HTTP_REQUEST_TYPE,
                NotificationPay::QUEUE_HTTP_REQUEST_TYPE,
            ]]);

        if ($idpartner > 0) {
            $query->andWhere('ps.IdOrg = :IDPARTNER', [':IDPARTNER' => $idpartner]);
        }
        $query->orderBy(['ID' => SORT_DESC]);

        if ($this->notifstate == 1) {
            //В очереди
            $query->andWhere('n.DateSend = 0');
        } elseif ($this->notifstate == 2) {
            //Отправленные
            $query->andWhere('n.DateSend > 0');
        }

        return $query->all();
    }

    public function GetError()
    {
        $err = $this->firstErrors;
        $err = array_pop($err);
        return $err;
    }

    public function RepeatNotif($id, $IsAdmin)
    {
        $idpartner = $IsAdmin ?  0 : UserLk::getPartnerId(Yii::$app->user);

        $query = new Query();
        $query
            ->select([
                'n.ID',
            ])
            ->from('`notification_pay` AS n')
            ->innerJoin('`pay_schet` AS ps', 'n.IdPay = ps.ID')
            ->where(['n.ID' => $id]);

        if ($idpartner > 0) {
            $query->andWhere('ps.IdOrg = :IDPARTNER', [':IDPARTNER' => $idpartner]);
        }

        $IdNotif = $query->scalar();
        if ($IdNotif) {
            Yii::$app->db->createCommand()
                ->update('notification_pay', [
                    'DateSend' => 0,
                    'SendCount' => 0,
                    'DateLastReq' => 0,
                    'FullReq' => null,
                    'HttpCode' => 0,
                    'HttpAns' => null
                ], '`ID` = :ID', [':ID' => $IdNotif]
                )->execute();

            return ['status' => 1, 'message' => 'Запрос колбэка возвращен в очередь'];
        }

        return ['status' => 0, 'message' => 'Ошибка запроса повтора операции'];
    }
}
