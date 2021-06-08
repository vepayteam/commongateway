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
    public $id = 0;
    public $Extid = '';
    public $httpCode = 0;
    public $testMode = false;

    public function rules()
    {
        return [
            [['partner', 'notifstate', 'id', 'httpCode'], 'integer'],
            [['Extid'], 'string', 'max' => 40],
            [['datefrom', 'dateto'], 'date', 'format' => 'php:d.m.Y H:i'],
            [['datefrom', 'dateto'], 'required'],
            [['testMode'], 'boolean'],
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
     * @param bool $isGeneratorResult
     *
     * @return array
     */
    public function GetList($IsAdmin, int $page = 0, bool $noLimit = false, bool $isGeneratorResult = false)
    {
        $pageLimit = 100;

        $idpartner = $IsAdmin ? $this->partner : UserLk::getPartnerId(Yii::$app->user);

        $datefrom = strtotime($this->datefrom);
        $dateto = strtotime($this->dateto);

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

        if ($this->id > 0) {
            $query->andWhere('n.IdPay = :ID', [':ID' => $this->id]);
        }
        if (!empty($this->Extid)) {
            $query->andWhere('ps.Extid = :EXTID', [':EXTID' => $this->Extid]);
        }

        if (!empty($this->httpCode) && $this->httpCode > 0) {
            $query->andWhere('n.HttpCode = :HTTPCODE', [':HTTPCODE' => $this->httpCode]);
        }

        $totalCountResult = (clone $query)->select(['COUNT(*) as cnt'])->all();
        $totalCount = (int) reset($totalCountResult)['cnt'];

        $select = [
            'n.ID',
            'n.IdPay',
            'n.DateCreate',
            'n.Email',
            'n.DateSend',
            'n.HttpCode',
            'n.HttpAns',
            'n.FullReq',
        ];

        if ($this->testMode === true) {
            $select = array_merge($select, ['ps.IdOrg', 'ps.Extid']);
        }

        $query->select($select);

        if ($noLimit === false) {
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
            'data'    => (($isGeneratorResult === true) ? self::mapQueryPaymentResult($query) : $query->all()),
            'payLoad' => new PaginationPayLoad([
                'totalCount' => $totalCount,
                'page'       => $page,
                'pageLimit'  => $pageLimit,
            ]),
        ];
    }

    /**
     * @param Query $query
     *
     * @return \Generator
     */
    private static function mapQueryPaymentResult(Query $query): \Generator
    {
        foreach ($query->each() as $row) {
            yield $row;
        }
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
