<?php

namespace app\modules\partner\controllers;

use app\models\api\Reguser;
use app\models\bank\Banks;
use app\models\mfo\statements\ReceiveStatemets;
use app\models\partner\admin\PerevodToPartner;
use app\models\partner\admin\SystemVoznagList;
use app\models\partner\admin\VoznagStat;
use app\models\partner\admin\VyvodList;
use app\models\partner\admin\VyvodVoznag;
use app\models\partner\stat\StatFilter;
use app\models\partner\UserLk;
use app\models\payonline\BalancePartner;
use app\models\payonline\Cards;
use app\models\payonline\Partner;
use app\models\SendEmail;
use app\models\sms\api\SingleMainSms;
use app\models\sms\tables\AccessSms;
use app\services\files\FileService;
use app\services\payment\forms\VoznagStatForm;
use app\services\validation\exceptions\TestSelValidateException;
use app\services\validation\TestSelValidationService;
use toriphes\console\Runner;
use Yii;
use yii\base\DynamicModel;
use yii\data\ArrayDataProvider;
use yii\db\Exception;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class AdminController extends Controller
{
    use SelectPartnerTrait;

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => false,
                        'roles' => ['?'],
                    ],
                    [
                        'allow' => true,
                        'roles' => ['@'],
                        'denyCallback' => function ($rule, $action) {
                            Yii::$app->getResponse()->redirect(Url::toRoute('/partner'), 302)->send();
                            return false;
                        },
                        'matchCallback' => function ($rule, $action) {
                            return UserLk::IsAdmin(Yii::$app->user);
                        }
                    ],
                ],
            ],
        ];
    }

    public function actionBank($id = 2)
    {
        $Bank = Banks::findOne(['ID' => (int)$id]);
        if (!$Bank) {
            throw new NotFoundHttpException();
        }
        return $this->render('bank', ['Bank' => $Bank]);
    }

    public function actionBanksave()
    {
        if (Yii::$app->request->isAjax && !Yii::$app->request->isPjax) {

            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

            $Bank = Banks::findOne(['ID' => (int)Yii::$app->request->post('IdBank')]);
            if ($Bank && $Bank->load(Yii::$app->request->post()) && $Bank->validate()) {
                $Bank->Save(false);
                return ['status' => 1, 'message' => ''];
            }
            return ['status' => 0, 'message' => 'Ошибка сохранения'];
        }
        return '';
    }

    /**
     * Вывод вознаграждения
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionComisotchet()
    {
        if (UserLk::IsAdmin(Yii::$app->user)) {
            $fltr = new StatFilter();
            return $this->render('comisotchet', [
                'partnerlist' => $fltr->getPartnersList(false, true)
            ]);
        } else {
            throw new NotFoundHttpException();
        }
    }

    /**
     * Вывод вознаграждения NEW
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionComisotchetNew()
    {
        if (UserLk::IsAdmin(Yii::$app->user)) {
            $fltr = new StatFilter();
            return $this->render('comisotchet', [
                'partnerlist' => $fltr->getPartnersList(false, true)
            ]);
        } else {
            throw new NotFoundHttpException();
        }
    }

    /**
     * @return array
     * @throws NotFoundHttpException
     * @throws \yii\db\Exception
     */
    public function actionComisotchetdata()
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $data = Yii::$app->request->post();
            $IsAdmin = UserLk::IsAdmin(Yii::$app->user);
            if (Yii::$app->request->post('TypeOtch') == 0) {
                $payShetList = new VoznagStat();
                if ($payShetList->load($data, '') && $payShetList->validate()) {
                    $payShetList->TypeUslug = 1;
                    $dataIn = $payShetList->GetOtchMerchant($IsAdmin);
                    $payShetList->TypeUslug = 2;
                    $dataOut = $payShetList->GetOtchMerchant($IsAdmin);
                    return ['status' => 1, 'data' => $this->renderPartial(
                        '_comisotchetdata', [
                        'IsAdmin' => $IsAdmin,
                        'dataIn' => $dataIn,
                        'dataOut' => $dataOut
                    ])];
                }
            } elseif (Yii::$app->request->post('TypeOtch') == 1) {

                $payShetList = new VyvodList();
                if ($payShetList->load($data, '') && $payShetList->validate()) {
                    $data = $payShetList->GetList($IsAdmin, 1);
                    return ['status' => 1, 'data' => $this->renderPartial(
                        '_comisotchetlist', [
                        'IsAdmin' => $IsAdmin,
                        'data' => $data,
                    ])];
                }

            } elseif (Yii::$app->request->post('TypeOtch') == 2) {

                $payShetList = new VyvodList();
                if ($payShetList->load($data, '') && $payShetList->validate()) {
                    $data = $payShetList->GetList($IsAdmin, 0);
                    return ['status' => 1, 'data' => $this->renderPartial(
                        '_comisotchetlist', [
                        'IsAdmin' => $IsAdmin,
                        'data' => $data,
                    ])];
                }

            } elseif (Yii::$app->request->post('TypeOtch') == 3) {

                $payShetList = new SystemVoznagList();
                if ($payShetList->load($data, '') && $payShetList->validate()) {
                    $data = $payShetList->GetList($IsAdmin);
                    return ['status' => 1, 'data' => $this->renderPartial(
                        '_comisotchetlist', [
                        'IsAdmin' => $IsAdmin,
                        'data' => $data
                    ])];
                }

            }

            return ['status' => 0, 'message' => 'Ошибка запроса'];

        } else {
            throw new NotFoundHttpException();
        }
    }

    /**
     * NEW
     * @return array
     * @throws NotFoundHttpException
     * @throws \yii\db\Exception
     */
    public function actionComisotchetdataNew()
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $post = Yii::$app->request->post();

            $IsAdmin = UserLk::IsAdmin(Yii::$app->user);
            switch (Yii::$app->request->post('TypeOtch')) {
                case VoznagStatForm::TYPE_REPORT:
                    $payShetList = new VoznagStat();
                    //@todo DRY!!!
                    if (!$payShetList->load($post, '') || !$payShetList->validate()) {
                        return ['status' => 0, 'message' => 'Ошибка запроса'];
                    }
                    break;
                case VoznagStatForm::TYPE_HISTORY_TRANSFER_CALC_ACCT:
                    $payShetList = new VyvodList();
                    if (!$payShetList->load($post, '') || !$payShetList->validate()) {
                        return ['status' => 0, 'message' => 'Ошибка запроса'];
                    }
                    $data = $payShetList->GetList($IsAdmin, VyvodList::TYPE_USLUG_NA_R_S_SCHET);
                    break;
                case VoznagStatForm::TYPE_HISTORY_TRANSFER_OUT_ACCT:
                    $payShetList = new VyvodList();
                    if (!$payShetList->load($post, '') || !$payShetList->validate()) {
                        return ['status' => 0, 'message' => 'Ошибка запроса'];
                    }
                    $data = $payShetList->GetList($IsAdmin, VyvodList::TYPE_USLUG_NA_VYDACHU);
                    break;
                case VoznagStatForm::TYPE_HISTORY_OUTPUT_REWARD:
                    $payShetList = new SystemVoznagList();
                    if (!$payShetList->load($post, '') || !$payShetList->validate()) {
                        return ['status' => 0, 'message' => 'Ошибка запроса'];
                    }
                    $data = $payShetList->GetList($IsAdmin);
                    break;
                default:
                    $payShetList = null;
            }

            if (Yii::$app->request->post('TypeOtch') == VoznagStatForm::TYPE_REPORT) {
                $payShetList->TypeUslug = VoznagStat::TYPE_SERVICE_POGAS;
                $dataIn = $payShetList->GetOtchMerchant($IsAdmin);
                $payShetList->TypeUslug = VoznagStat::TYPE_SERVICE_VYDACHA;
                $dataOut = $payShetList->GetOtchMerchant($IsAdmin);
                $view = '_comisotchetdata';
                $params = [
                    'IsAdmin' => $IsAdmin,
                    'dataIn' => $dataIn,
                    'dataOut' => $dataOut,
                ];
            } else {
                $view = '_comisotchetlist';
                $params = [
                    'IsAdmin' => $IsAdmin,
                    'data' => $data,
                ];
            }
            return ['status' => 1, 'data' => $this->renderPartial($view, $params)];
        } else {
            throw new NotFoundHttpException();
        }
    }

    /**
     * Вывод вознаграждения
     * @return array
     * @throws NotFoundHttpException
     * @throws \yii\db\Exception
     */
    public function actionVyvodvoznag()
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $data = Yii::$app->request->post();
            $vyvod = new VyvodVoznag();
            if ($vyvod->load($data, '') && $vyvod->validate() && $vyvod->CreatePayVyvod()) {
                return ['status' => 1, 'message' => 'Средства выведены'];
            }
            return ['status' => 0, 'message' => 'Ошибка создания операции вывода'];
        } else {
            throw new NotFoundHttpException();
        }
    }

    /**
     * @return array
     * @throws NotFoundHttpException
     */
    public function actionPerevodaginfo()
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            $Partner = Partner::findOne(['ID' => (int)Yii::$app->request->post('partner')]);
            if (!$Partner) {
                return ['status' => 0, 'message' => 'Не найден'];
            }

            $recviz = $Partner->getPartner_bank_rekviz()->one();

            return ['status' => 1, 'data' => [
                'balance' => ($Partner->IsCommonSchetVydacha ? $Partner->BalanceOut / 100.0 : $Partner->BalanceIn / 100.0),
                'schettcb' => ($Partner->IsCommonSchetVydacha ? '' : (string)$Partner->SchetTcb),
                'schetfrom' => ($Partner->IsCommonSchetVydacha ? (string)$Partner->SchetTcb : (string)$Partner->SchetTcbTransit),
                'urlico' => (string)$Partner->UrLico,
                'schetrs' => isset($recviz) ? $recviz->RaschShetPolushat : '',
                'schetbik' => isset($recviz) ? $recviz->BIKPoluchat : '',
                'schetinfo' => isset($recviz) ? $recviz->NameBankPoluchat : '',
            ]];
        }
        throw new NotFoundHttpException();
    }

    public function actionPerevodacreate()
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $Perevod = new PerevodToPartner();
            $Perevod->load(Yii::$app->request->post(), 'Perechislen');
            if ($Perevod->validate()) {
                return $Perevod->CreatePerevod();
            }
            return ['status' => 0, 'message' => $Perevod->GetError()];
        }
        throw new NotFoundHttpException();
    }

    public function actionRenotificate()
    {
        $date = strtotime(Yii::$app->request->get('datefrom', ''));
        if ($date > 0) {
            Yii::$app->db->createCommand()
                ->update('notification_pay', [
                    'DateSend' => 0,
                    'SendCount' => 0,
                    'DateLastReq' => 0,
                    'FullReq' => null,
                    'HttpCode' => 0,
                    'HttpAns' => null
                ], '`TypeNotif` = 2 AND `DateCreate` > :DATE AND HttpCode = 0 AND DateSend > 0',
                    [':DATE' => $date]
                )->execute();

            return 1;
        }
        return 0;
    }

    public function actionTestsel()
    {
        $rawSql = $_GET['s'];

        $validationService = new TestSelValidationService();
        try {
            $validatedSql = $validationService->validateSql($rawSql);
        } catch (TestSelValidateException $e) {
            Yii::$app->errorHandler->logException($e);
            Yii::$app->response->statusCode = 403;

            return $e->getMessage();
        }

        try {
            $command = Yii::$app->db->createCommand($validatedSql);
            $res = $command->queryAll();
        } catch (\Exception $e) {
            Yii::$app->errorHandler->logException($e);

            return $e->getMessage();
        }

        // TODO может сделать рендер через view?
        $ret = "<table border='1'>";
        foreach ($res as $row) {
            $ret .= "<tr>";
            foreach ($row as $k => $r) {
                $ret .= "<td>" . $k . "<td>";
            }
            $ret .= "</tr>";
            break;
        }
        foreach ($res as $row) {
            $ret .= "<tr>";
            foreach ($row as $r) {
                $ret .= "<td>" . $r . "<td>";
            }
            $ret .= "</tr>";
        }
        $ret .= "</table>";
        return $ret;
    }

    public function actionPerformsql()
    {
        return $this->render('perfmon_db_server.php', ['mysql' => Yii::$app->db]);
    }

    /**
     * Отчет для проверки синхронности данных о движении по счетам между нашей БД и Банком
     * //TODO: вынести в отдельный класс и вызывать через консоль
     * @param $partner_id
     * @param null $from
     * @param null $to
     * @throws BadRequestHttpException
     * @throws Exception
     */
    public function actionStatementdiff($id, $from = null, $to = null)
    {
        $partner = Partner::findOne(['ID' => $id]);
        if (!$partner) {
            throw new BadRequestHttpException('Не указан партнёр');
        }

        $dateFrom = $from ? strtotime($from) : strtotime('1990-01-01 00:00:00');
        $dateTo = $to ? strtotime($to) : time();

        $list = (new ReceiveStatemets($partner))->getAll($dateFrom, $dateTo);


        if (empty($list)) {
            throw new BadRequestHttpException("Выписка из банка пуста");
        }

        $list = ArrayHelper::index($list, 'id');


        $appendListWithInternalData = function ($balanceType, &$list) use ($id, $dateFrom, $dateTo) {
            $data = (new Query())
                ->select('orders.*, sa.BnkId, sa.TypeAccount')
                ->from($balanceType . ' orders')
                ->leftJoin('statements_account sa', 'orders.IdStatm = sa.ID')
                ->where(['orders.IdPartner' => $id])
                ->andWhere('orders.DateOp BETWEEN :DATEFROM AND :DATETO', [
                        ':DATEFROM' => $dateFrom,
                        ':DATETO' => $dateTo]
                )->indexBy('BnkId')
                ->all();

            if (!empty($data)) {
                foreach ($data as $data_id => $row) {
                    //если есть в нашей БД транзакция
                    if (isset($list[$data_id])) {
                        $list[$data_id]['our_balance_type'] = $balanceType;
                        $list[$data_id]['our_data'] = $row;
                    }
                }
            }
        };

        $appendListWithInternalData('partner_orderin', $list);
        $appendListWithInternalData('partner_orderout', $list);

        return Json::encode($list);
    }

    public function actionExec()
    {
        if (Yii::$app->request->isPost) {
            $post = Yii::$app->request->post();
            $oldApp = \Yii::$app;
            $config = require(Yii::getAlias('@app/config/console.php'));
            new \yii\console\Application($config);

            try {
                \Yii::$app->runAction($post['name'], [$post['param1'], $post['param2'], $post['param3']]);
            } catch (\Exception $e) {
                echo 'Ошибка выполнения';
            }
            \Yii::$app = $oldApp;
        } else {
            return $this->render('exec');
        }
    }

    public function actionExecRedis()
    {
        if (Yii::$app->request->isPost) {
            $post = Yii::$app->request->post();
            try {
                $redis = Yii::$app->redis;
                $params = explode("\n", $post['params']);
                $result = $redis->executeCommand($post['name'], array_map('trim', $params));
                echo '<pre>';
                print_r($result);
                die;
            } catch (\Exception $e) {
                echo 'Ошибка выполнения';
            }

        } else {
            return $this->render('exec_redis');
        }
    }

    /**
     * @param string $queueName
     * @return string
     * @throws BadRequestHttpException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionQueueInfo($queueName = 'queue')
    {
        $this->layout = 'queue';

        if(!in_array($queueName, ['queue', 'reportQueue'])){
            throw new BadRequestHttpException();
        }
        $queue = Yii::$app->get($queueName);

        $prefix = $queue->channel;
        $waiting = $queue->redis->llen("$prefix.waiting");
        $delayed = $queue->redis->zcount("$prefix.delayed", '-inf', '+inf');
        $reserved = $queue->redis->zcount("$prefix.reserved", '-inf', '+inf');
        $total = $queue->redis->get("$prefix.message_id");
        $done = $total - $waiting - $delayed - $reserved;
        $dataProvider = new ArrayDataProvider([
            'allModels' => [
                ['status' => 'waiting', 'count' => $waiting],
                ['status' => 'delayed', 'count' => $delayed],
                ['status' => 'reserved', 'count' => $reserved],
                ['status' => 'done', 'count' => $done],
                ['status' => 'total', 'count' => $total]
            ],
            'sort' => [
                'attributes' => ['status', 'count']
            ]
        ]);
        return $this->render('queueinfo', ['dataProvider' => $dataProvider]);
    }

    /**
     * @param string $queueName
     * @return string
     * @throws BadRequestHttpException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionGetQueueAllMessages($queueName = 'queue')
    {
        $this->layout = 'queue';

        if(!in_array($queueName, ['queue', 'reportQueue'])){
            throw new BadRequestHttpException();
        }
        $queue = Yii::$app->get($queueName);

        $prefix = $queue->channel;
        $messages = $queue->redis->hgetall("$prefix.messages");
        $i = 0;
        $allModels = [];
        while (isset($messages[$i]) && isset($messages[$i + 1])) {
            $allModels[] = ['key' => $messages[$i], 'value' => $messages[$i + 1]];
            $i = $i + 2;
        }
        $dataProvider = new ArrayDataProvider([
            'allModels' => $allModels,
            'pagination' => [
                'pageSize' => 20,
            ],
            'sort' => [
                'attributes' => ['status', 'count']
            ]
        ]);
        return $this->render('queueallmessages', ['dataProvider' => $dataProvider]);
    }

    /**
     * @param string $queueName
     * @return string
     * @throws BadRequestHttpException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionGetQueueWaitingMessages($queueName = 'queue')
    {
        $this->layout = 'queue';

        if(!in_array($queueName, ['queue', 'reportQueue'])){
            throw new BadRequestHttpException();
        }
        $queue = Yii::$app->get($queueName);

        $prefix = $queue->channel;
        $messages = [];
        $waitingRange = $queue->redis->lrange("$prefix.waiting", 0, -1);
        if (is_array($waitingRange) && count($waitingRange) > 0) {
            $messages = $queue->redis->hmget("$prefix.messages", ... $waitingRange);
        }
        $i = 0;
        $allModels = [];
        while (isset($waitingRange[$i])) {
            $allModels[] = ['key' => $waitingRange[$i], 'value' => $messages[$i]];
            $i++;
        }
        $dataProvider = new ArrayDataProvider([
            'allModels' => $allModels,
            'pagination' => [
                'pageSize' => 20,
            ],
            'sort' => [
                'attributes' => ['status', 'count']
            ]
        ]);
        return $this->render('queuewaitingmessages', ['dataProvider' => $dataProvider]);
    }

    /**
     * @param string $queueName
     * @return string
     * @throws BadRequestHttpException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionGetQueueReservedMessages($queueName = 'queue')
    {
        $this->layout = 'queue';

        if(!in_array($queueName, ['queue', 'reportQueue'])){
            throw new BadRequestHttpException();
        }
        $queue = Yii::$app->get($queueName);

        $prefix = $queue->channel;
        $messages = [];
        $reservedRange = $queue->redis->zrange("$prefix.reserved", 0, -1);
        if (is_array($reservedRange) && count($reservedRange) > 0) {
            $messages = $queue->redis->hmget("$prefix.messages", ... $reservedRange);
        }
        $i = 0;
        $allModels = [];
        while (isset($reservedRange[$i])) {
            $allModels[] = ['key' => $reservedRange[$i], 'value' => $messages[$i]];
            $i++;
        }
        $dataProvider = new ArrayDataProvider([
            'allModels' => $allModels,
            'pagination' => [
                'pageSize' => 20,
            ],
            'sort' => [
                'attributes' => ['status', 'count']
            ]
        ]);
        return $this->render('queuereservedmessages', ['dataProvider' => $dataProvider]);
    }
}
