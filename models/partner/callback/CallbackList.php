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
    public $partner = [];
    public $id = 0;
    public $Extid = '';
    public $httpCode = [];
    public $testMode = false;

    public const MAX_BATCH_CALLBACK_COUNT = 1000;

    public function rules()
    {
        return [
            [['notifstate', 'id'], 'integer'],
            [['Extid'], 'string', 'max' => 40],
            [['datefrom', 'dateto'], 'date', 'format' => 'php:d.m.Y H:i'],
            [['datefrom', 'dateto'], 'required'],
            [['testMode'], 'boolean'],
            ['partner', 'each', 'rule' => ['integer']],
            ['httpCode', 'each', 'rule' => ['integer']],
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

        if ( !empty($idpartner) ) {
            $query->andWhere(['in', 'ps.IdOrg', $idpartner]);
        }

        if ($this->id > 0) {
            $query->andWhere(['n.IdPay' => $this->id]);
        }
        if (!empty($this->Extid)) {
            $query->andWhere(['ps.Extid' => $this->Extid]);
        }

        if (!empty($this->httpCode)) {
            $query->andWhere(['in', 'n.HttpCode', $this->httpCode]);
        }

        $totalCount = (int) (clone $query)->select(['COUNT(*) as cnt'])->scalar();

        $select = [
            'n.ID',
            'n.IdPay',
            'n.DateCreate',
            'n.Email',
            'n.DateSend',
            'n.HttpCode',
            'n.HttpAns',
            'n.FullReq',
            'ps.Extid',
        ];

        if ($this->testMode === true) {
            $select = array_merge($select, ['ps.IdOrg']);
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
            'data'    => $isGeneratorResult ? self::mapQueryPaymentResult($query) : $query->all(),
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
