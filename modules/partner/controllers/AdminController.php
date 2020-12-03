<?php

namespace app\modules\partner\controllers;

use app\models\api\Reguser;
use app\models\bank\Banks;
use app\models\bank\TCBank;
use app\models\bank\TcbGate;
use app\models\crypt\UserKeyLk;
use app\models\mfo\MfoReq;
use app\models\mfo\statements\ReceiveStatemets;
use app\models\Options;
use app\models\partner\admin\PerevodToPartner;
use app\models\partner\admin\SystemVoznagList;
use app\models\partner\admin\Uslugi;
use app\models\partner\admin\VoznagStat;
use app\models\partner\admin\VyvodList;
use app\models\partner\admin\VyvodVoznag;
use app\models\partner\stat\StatFilter;
use app\models\partner\UserLk;
use app\models\payonline\BalancePartner;
use app\models\payonline\Cards;
use app\models\payonline\Partner;
use app\models\payonline\Uslugatovar;
use app\models\SendEmail;
use app\models\sms\api\SingleMainSms;
use app\models\sms\tables\AccessSms;
use toriphes\console\Runner;
use Yii;
use yii\db\Exception;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\HttpException;
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
                'balance' => ($Partner->IsCommonSchetVydacha ? $Partner->BalanceOut/100.0 : $Partner->BalanceIn/100.0),
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

    public function actionTestretwork($id)
    {
        Yii::$app->db->createCommand()->update('pay_schet', ['Status' => 0, 'ErrorInfo' => ''], '`ID` = :ID', [':ID' => $id])->execute();
        return "1";
    }

    public function actionTestcancelpay($id)
    {
        Yii::$app->db->createCommand()->update('pay_schet', ['Status' => 2, 'ErrorInfo' => 'Отмена платежа'], '`ID` = :ID AND `Status` = 0', [':ID' => $id])->execute();
        return "1";
    }

    public function actionTestps($id)
    {
        Yii::$app->db->createCommand()->update('pay_schet', ['sms_accept' => 1], '`ID` = :ID', [':ID' => $id])->execute();
        return "1";
    }

    public function actionTestphp()
    {
        return phpinfo();
    }

    public function actionTestapplog($id = 0)
    {
        if ($id == 0) {
            return Yii::$app->response->sendFile(Yii::$app->basePath . "/runtime/logs/app.log");
        } else {
            return Yii::$app->response->sendFile(Yii::$app->basePath . "/runtime/logs/app.log.".intval($id));
        }
    }

    public function actionTestmfolog($id = 0)
    {
        if ($id == 0) {
            return Yii::$app->response->sendFile(Yii::$app->basePath . "/runtime/logs/mfo.log");
        } else {
            return Yii::$app->response->sendFile(Yii::$app->basePath . "/runtime/logs/mfo.log.".intval($id));
        }
    }

    public function actionTestmerchantlog($id = 0)
    {
        if ($id == 0) {
            return Yii::$app->response->sendFile(Yii::$app->basePath . "/runtime/logs/console/merchant.log");
        } else {
            return Yii::$app->response->sendFile(Yii::$app->basePath . "/runtime/logs/console/merchant.log.".intval($id));
        }
    }

    public function actionTestrsbcronlog($id = 0)
    {
        if ($id == 0) {
            return Yii::$app->response->sendFile(Yii::$app->basePath . "/runtime/logs/console/rsbcron.log");
        } else {
            return Yii::$app->response->sendFile(Yii::$app->basePath . "/runtime/logs/console/rsbcron.log.".intval($id));
        }
    }

    public function actionTestregcard()
    {
        $mfo = Yii::$app->request->get('mfo');
        $reguser = new Reguser();
        $user = $reguser->findUser('0', $mfo.'-'.time(), md5($mfo.'-'.time()), $mfo, false);

        Yii::$app->db->createCommand()->insert('cards', [
            'IdUser' => $user->ID,
            'NameCard' => Yii::$app->request->get('number'),
            'ExtCardIDP' => Yii::$app->request->get('idcard'),
            'CardNumber' => Yii::$app->request->get('number'),
            'CardType' => 0,
            'SrokKard' => Yii::$app->request->get('expiry'),
            'Status' => 1,
            'DateAdd' => time(),
            'Default' => 0,
            'IdBank' => Yii::$app->request->get('bank')
        ])->execute();

        echo "card=".Yii::$app->db->lastInsertID;

        $card = Cards::findOne(['ID' => Yii::$app->db->lastInsertID]);
        print_r($card->toArray());

        return "";
    }

    public function actionTestvyplata()
    {
        $output = '';
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $runner = new Runner(['phpexec' => 'php.exe']);
            $runner->run('widget/vyvod', $output);
            $output = iconv('cp866', 'windows-1251', $output);
            $output = iconv('windows-1251', 'utf-8', $output);
        } else {
            $runner = new Runner();
            $runner->run('widget/vyvod', $output);
        }

        return $output; //prints the command output
    }

    public function actionTestvovzvrcomis()
    {
        $output = '';
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $runner = new Runner(['phpexec' => 'php.exe']);
            $runner->run('widget/return-comis', $output);
            $output = iconv('cp866', 'windows-1251', $output);
            $output = iconv('windows-1251', 'utf-8', $output);
        } else {
            $runner = new Runner();
            $runner->run('widget/return-comis', $output);
        }

        return $output; //prints the command output
    }

    public function actionTestvyvodvirt()
    {
        $output = '';
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $runner = new Runner(['phpexec' => 'php.exe']);
            $runner->run('widget/vyvodvirt', $output);
            $output = iconv('cp866', 'windows-1251', $output);
            $output = iconv('windows-1251', 'utf-8', $output);
        } else {
            $runner = new Runner();
            $runner->run('widget/vyvodvirt', $output);
        }

        return $output; //prints the command output
    }

    public function actionTestresetvyvod($id)
    {
        if ($id > 0) {
            Yii::$app->db->createCommand()->insert('vyvod_reestr', [
                'IdPartner' => (int)$id,
                'DateFrom' => strtotime('yesterday'),
                'DateTo' => strtotime('today') - 1,
                'DateOp' => time(),
                'SumOp' => 0,
                'StateOp' => 1,
                'IdPay' => 0
            ])->execute();
        }
        return $id;
    }

    public function actionTestresetvozn($id)
    {
        if ($id > 0) {
            Yii::$app->db->createCommand()->insert('vyvod_system', [
                'IdPartner' => (int)$id,
                'DateOp' => time(),
                'DateFrom' => strtotime(Yii::$app->request->get('datefrom', '')),
                'DateTo' => strtotime(Yii::$app->request->get('dateto', '')),
                'TypeVyvod' => (int)Yii::$app->request->get('type', 0),
                'SatateOp' => 1,
                'IdPay' => 0,
                'Summ' => (int)Yii::$app->request->get('summ', 0)
            ])->execute();
        }
        return $id;
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

    public function actionTestdelresetvozn($id)
    {
        if ($id > 0) {
            Yii::$app->db->createCommand()->delete('vyvod_system', [
                'IdPartner' => (int)$id,
                'DateFrom' => mktime(0, 0, 0, date('n') - 1, 1, date('Y'))
            ])->execute();
        }
        return $id;
    }

    public function actionTestaddbalance($id)
    {
        if ($id > 0) {
            $b = new BalancePartner(Yii::$app->request->get('type'), $id);
            $b->Inc(Yii::$app->request->get('sum', 0), Yii::$app->request->get('info', ''), 0, 0, 0);
        }
        return $id;
    }

    public function actionTestdecbalance($id)
    {
        if ($id > 0) {
            $b = new BalancePartner(Yii::$app->request->get('type'), $id);
            $b->Dec(Yii::$app->request->get('sum', 0), Yii::$app->request->get('info', ''), 0, 0, 0);
        }
        return $id;
    }

    public function actionTestmail()
    {
        $mail = new SendEmail();
        echo "Test email: " . $mail->sendReestr("support@teleport.run", "Test email vepay", "Test email vepay");
    }

    public function actionTestmainsms()
    {
        $access = AccessSms::find()->where(['partner_id' => 5, 'description'=>'MainSms.ru'])->one();
        if (!$access) {
            return "0";
        }

        $mainsms = new SingleMainSms("test mainsms", ["79127012918"], $access);
        $mainsms->send();
        return "1";
    }

    public function actionTestcacncelschet($id)
    {
        Yii::$app->db->createCommand()->delete('act_schet', '`ID` = :ID', [':ID' => $id])->execute();
        return "1";
    }

    public function actionTestrecallcvozn()
    {
        $res = Yii::$app->db->createCommand('
            SELECT
                ps.ID, 
                ps.SummPay, 
                ps.ComissSumm,
                ut.IsCustom
            FROM
                `pay_schet` AS ps
            LEFT JOIN uslugatovar AS ut ON ut.ID = ps.IdUsluga
            WHERE 
                ps.DateCreate BETWEEN :DATEFROM AND :DATETO
                AND ut.IsCustom = :CUST
                AND ut.IDPartner = :PART  
        ', [
            ':CUST' => Yii::$app->request->get('IsCustom', -1),
            ':PART' => Yii::$app->request->get('Id', -1),
            ':DATEFROM' => strtotime(Yii::$app->request->get('datefrom', '')),
            ':DATETO' => strtotime(Yii::$app->request->get('dateto', '')),
        ])->query();

        $ProvComisPC = Yii::$app->request->get('ProvComisPC', 0.00);
        $ProvComisMin = Yii::$app->request->get('ProvComisMin', 0.00);

        $ProvVoznagPC = Yii::$app->request->get('ProvVoznagPC', 0.00);
        $ProvVoznagMin = Yii::$app->request->get('ProvVoznagMin', 0.00);

        $tr = Yii::$app->db->beginTransaction();
        while ($row = $res->read()) {
            $row['BankComis'] = round(($row['SummPay'] + $row['ComissSumm']) * $ProvComisPC / 100.0, 0);
            if ($row['BankComis'] < $ProvComisMin * 100.0) {
                $row['BankComis'] = $ProvComisMin * 100.0;
            }
            $row['MerchVozn'] = round($row['SummPay'] * $ProvVoznagPC / 100.0, 0);
            if ($row['MerchVozn'] < $ProvVoznagMin * 100.0) {
                $row['MerchVozn'] = $ProvVoznagMin * 100.0;
            }

            Yii::$app->db->createCommand()->update('pay_schet', [
                'BankComis' => $row['BankComis'],
                'MerchVozn' => $row['MerchVozn'],
            ], '`ID` = :ID', [':ID' => $row['ID']])->execute();
        }
        $tr->commit();

        return "1";
    }

    public function actionTestrecallcvozncurr()
    {
        $res = Yii::$app->db->createCommand('
            SELECT
                ps.ID, 
                ps.SummPay, 
                ps.ComissSumm,
                ut.IsCustom,
                ut.ProvComisPC,
                ut.ProvComisMin,
                ut.ProvVoznagPC,
                ut.ProvVoznagMin
            FROM
                `pay_schet` AS ps
            LEFT JOIN uslugatovar AS ut ON ut.ID = ps.IdUsluga
            WHERE 
                ps.DateCreate BETWEEN :DATEFROM AND :DATETO
        ', [
            ':DATEFROM' => strtotime(Yii::$app->request->get('datefrom', '')),
            ':DATETO' => strtotime(Yii::$app->request->get('dateto', '')),
        ])->query();

        $tr = Yii::$app->db->beginTransaction();
        while ($row = $res->read()) {

            $ProvComisPC = $row['ProvComisPC'];
            $ProvComisMin = $row['ProvComisMin'];

            $ProvVoznagPC = $row['ProvVoznagPC'];
            $ProvVoznagMin = $row['ProvVoznagMin'];

            $row['BankComis'] = round(($row['SummPay'] + $row['ComissSumm']) * $ProvComisPC / 100.0, 0);
            if ($row['BankComis'] < $ProvComisMin * 100.0) {
                $row['BankComis'] = $ProvComisMin * 100.0;
            }
            $row['MerchVozn'] = round($row['SummPay'] * $ProvVoznagPC / 100.0, 0);
            if ($row['MerchVozn'] < $ProvVoznagMin * 100.0) {
                $row['MerchVozn'] = $ProvVoznagMin * 100.0;
            }

            Yii::$app->db->createCommand()->update('pay_schet', [
                'BankComis' => $row['BankComis'],
                'MerchVozn' => $row['MerchVozn'],
            ], '`ID` = :ID', [':ID' => $row['ID']])->execute();
        }

        $tr->commit();

        return "1";
    }

    public function actionTestsel()
    {
        try {
            $res = Yii::$app->db->createCommand($_GET['s'])->queryAll();
        } catch (Exception $e) {
            return $e->getMessage();
        }

        $ret = "<table border='1'>";
        foreach ($res as $row) {
            $ret .= "<tr>";
            foreach ($row as $k => $r) {
                $ret .= "<td>".$k."<td>";
            }
            $ret .= "</tr>";
            break;
        }
        foreach ($res as $row) {
            $ret .= "<tr>";
            foreach ($row as $r) {
                $ret .= "<td>".$r."<td>";
            }
            $ret .= "</tr>";
        }
        $ret .= "</table>";
        return $ret;
    }

    public function actionTestupd()
    {
        try {
            Yii::$app->db->createCommand($_GET['s'])->execute();
        } catch (Exception $e) {
            return $e->getMessage();
        }
        return $_GET['s'];
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


        $appendListWithInternalData = function ($balanceType,  &$list) use ($id, $dateFrom, $dateTo) {
            $data = (new Query())
                ->select('orders.*, sa.BnkId, sa.TypeAccount')
                ->from($balanceType .' orders')
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
}
