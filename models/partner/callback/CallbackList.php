<?php

namespace app\models\partner\callback;

use app\models\partner\UserLk;
use app\modules\partner\controllers\structures\PaginationPayLoad;
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
            'dateto'   => 'Период',
        ];
    }

    /**
     * @param      $IsAdmin
     * @param int  $page
     * @param bool $noLimit
     *
     * @return array
     */
    public function GetList($IsAdmin, int $page = 0, bool $noLimit = false)
    {
        $pageLimit = 100;

        $idpartner = $IsAdmin ? $this->partner : UserLk::getPartnerId(Yii::$app->user);

        $datefrom = strtotime($this->datefrom . " 00:00:00");
        $dateto = strtotime($this->dateto . " 23:59:59");
        if ($datefrom < $dateto - 365 * 86400) {
            $datefrom = $dateto - 365 * 86400;
        }

        $query = new Query();

        $query
            ->from('`notification_pay` AS n')
            ->leftJoin('`pay_schet` AS ps', 'n.IdPay = ps.ID')
            ->where('n.DateCreate BETWEEN :DATEFROM AND :DATETO', [
                ':DATEFROM' => $datefrom,
                ':DATETO'   => $dateto,
            ])
            ->andWhere([
                'TypeNotif' => [
                    NotificationPay::CRON_HTTP_REQUEST_TYPE,
                    NotificationPay::QUEUE_HTTP_REQUEST_TYPE,
                ],
            ]);

        if ( $idpartner > 0 ) {
            $query->andWhere('ps.IdOrg = :IDPARTNER', [':IDPARTNER' => $idpartner]);
        }

        $totalCountResult = (clone $query)->select(['COUNT(*) as cnt'])->all();
        $totalCount = (int) reset($totalCountResult)['cnt'];

        $query->select([
            'n.ID',
            'n.IdPay',
            'n.DateCreate',
            'n.Email',
            'n.DateSend',
            'n.HttpCode',
            'n.HttpAns',
            'n.FullReq',
        ]);

        if ( $noLimit === false ) {
            if ( $page > 0 ) {
                $query->offset($pageLimit * ($page - 1));
            }
            $query->orderBy(['ID' => SORT_DESC])->limit($pageLimit);
        } else {
            $query->orderBy(['ID' => SORT_DESC]);
        }

        if ($this->notifstate == 1) {
            //В очереди
            $query->andWhere('n.DateSend = 0');
        } elseif ($this->notifstate == 2) {
            //Отправленные
            $query->andWhere('n.DateSend > 0');
        }

        return [
            'data'    => $query->all(),
            'payLoad' => new PaginationPayLoad([
                'totalCount' => $totalCount,
                'page'       => $page,
                'pageLimit'  => $pageLimit,
            ]),
        ];
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
