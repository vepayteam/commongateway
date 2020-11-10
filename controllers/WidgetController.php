<?php

namespace app\controllers;

use app\models\bank\TCBank;
use app\models\bank\TcbGate;
use app\models\kfapi\KfPay;
use app\models\payonline\CreatePay;
use app\models\payonline\OrderPay;
use app\models\payonline\Partner;
use app\models\payonline\PayForm;
use app\models\payonline\RefererPoint;
use app\models\payonline\Uslugatovar;
use app\models\Payschets;
use app\models\TU;
use Yii;
use yii\db\Exception;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class WidgetController extends Controller
{
    public $layout = 'widgetlayout';

    private $bank = 2; //0 - РСБ , 1 - Россия 2 - ТКБ

    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    /**
     * @param $action
     * @return bool
     * @throws BadRequestHttpException
     */
    public function beforeAction($action)
    {
        if (in_array($action->id, ['form', 'orderdone', 'orderok'])) {
            $this->enableCsrfValidation = false;
        }
        return parent::beforeAction($action);
    }


    /**
     * Виджет для оплаты
     * @param $id
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionOrder($id)
    {
        $order = OrderPay::findOne($id);
        if (!$order || $order->IdDeleted == 1) {
            throw new NotFoundHttpException("Счет не найден");
        }
        if ($order && $order->StateOrder != 0) {
            throw new NotFoundHttpException("Счет не может быть оплачен");
        }

        $uslug = Uslugatovar::findOne(['IDPartner' => $order->IdPartner, 'IsCustom' => 2, 'IsDeleted' => 0]);
        if (!$uslug) {
            throw new NotFoundHttpException('Магазин не найден');
        }

        $payform = new PayForm();

        return $this->render('index', [
            'order' => $order,
            'isorder' => 1,
            'payform' => $payform,
            'bank' => $this->bank
        ]);
    }

    public function actionPay()
    {
        $prov = Yii::$app->request->get('prov', 0);
        $id = Yii::$app->request->get('id', 0);
        $sum = Yii::$app->request->get('sum', 0);
        $info = htmlspecialchars(Yii::$app->request->get('info', ''));
        $refer = Yii::$app->request->get('refer');

        $partner = Partner::findOne(['ID' => $prov, 'IsDeleted' => 0]);
        $uslug = Uslugatovar::findOne(['IDPartner' => $prov, 'IsCustom' => 2, 'IsDeleted' => 0]);
        if (!$uslug || !$partner) {
            throw new NotFoundHttpException('Магазин не найден');
        }
        if ($sum <= 0) {
            throw new NotFoundHttpException('Неверная сумма платежа');
        }
        $order = new OrderPay();
        $order->IdPartner = $partner->ID;
        $order->SumOrder = $sum;
        $order->Comment = 'Заказ '.$id.". ".$info;
        if ($refer) {
            $refererAgent = new RefererPoint();
            $agent = $refererAgent->getAgentBySite($refer);
        }

        /*Yii::$app->view->params['colors'] = '';
        if (!empty($uslug->ColorWdtMain)) {
            Yii::$app->view->params['colors'] = [$uslug->ColorWdtMain, $uslug->ColorWdtActive];
        }*/
        $payform = new PayForm();
        return $this->render('index', [
            'order' => $order,
            'isorder' => 0,
            'payform' => $payform,
            'bank' => $this->bank,
        ]);
    }

    /**
     * Форма оплаты своя (PCI DSS)
     * @return array|Response
     * @throws NotFoundHttpException
     * @throws \yii\db\Exception
     */
    public function actionCreatepay()
    {
        // TODO: переписать под стратегии
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            if (Yii::$app->request->post('isorder', 1) == 1) {
                $order = OrderPay::findOne(['ID' => (int)Yii::$app->request->post('IdOrder')]);
            } else {
                //без счета
                $order = new OrderPay();
                if (!($order->load(Yii::$app->request->post(), 'Order') && $order->validate())) {
                    return ['status' => 0, 'message' => 'Ошибка данных заказа'];
                }
            }
            if (!$order) {
                return ['status' => 0, 'message' => 'Счет не найден'];
            }

            if (!$order->IdPaySchet) {
                $uslug = Uslugatovar::findOne(['IDPartner' => $order->IdPartner, 'IsCustom' => 2, 'IsDeleted' => 0]);

                if (!$uslug) {
                    return ['status' => 0, 'message' => 'Магазин не найден'];
                }

                $kfPay = new KfPay();
                $kfPay->scenario = KfPay::SCENARIO_FORM;
                $kfPay->setAttributes([
                    'amount' => $order->SumOrder / 100.00,
                    'descript' => $order->Comment,
                    'successurl' => $uslug->UrlReturn,
                    'failurl' => $uslug->UrlReturnFail
                ]);

                $pay = new CreatePay();
                $data = $pay->payToMfo(null, [$order->ID, $order->Comment], $kfPay, $uslug->ID, $this->bank, $order->IdPartner, 0);
                if ($data) {
                    $order->IdPaySchet = $data['IdPay'];
                    if ($order->ID) {
                        $pay->setIdOrder($order->ID, $data);
                    }
                } else {
                    return ['status' => 0, 'message' => 'Ошибка создания заказа'];
                }
            }

            $payform = new PayForm();
            $payform->load(Yii::$app->request->post(), 'PayForm');
            $payform->IdPay = $order->IdPaySchet;
            if (!$payform->validate()) {
                return ['status' => 0, 'message' => $payform->GetError()];
            }

            Yii::warning("widgetCreatepay create id=" . $payform->IdPay);
            //данные счета для оплаты
            $payschets = new Payschets();
            $params = $payschets->getSchetData($payform->IdPay,null);
            if ($params && $params['DateCreate'] + $params['TimeElapsed'] > time()) {

                Yii::$app->session->set('IdWidgetPay', $params['ID']);

                if ($params['Status'] != 0 || $params['UserClickPay'] != 0 || !TU::IsInPay($params['IsCustom'])) {
                    //нельзя оплачивать
                    return $this->redirect(\yii\helpers\Url::to('/pay/orderok?id='.$params['ID']));
                }

                $params['card']['number'] = $payform->CardNumber;
                $params['card']['holder'] = $payform->CardHolder;
                $params['card']['year'] = $payform->CardYear;
                $params['card']['month'] = $payform->CardMonth;
                $params['card']['cvc'] = $payform->CardCVC;

                //занести данные карты
                $payschets->SetCardPay($params['ID'], $params['card']);

                //$params['Bank'] == 2
                $TcbGate = new TcbGate($params['IDPartner'],null, $params['IsCustom']);
                $tcBank = new TCBank($TcbGate);
                $ret = $tcBank->PayXml($params);

                if ($ret['status'] == 1) {
                    $payschets->SetStartPay($params['ID'], $ret['transac'], $payform->Email);
                    //отправить запрос адреса формы 3ds
                    return [
                        'status' => 1,
                        'url' => $ret['url'],
                        'pa' => $ret['pa'],
                        'md' => $ret['md'],
                        'creq' => '',
                        'termurl' => $payform->GetWidgetRetUrl($params['ID']),
                    ];
                } elseif ($ret['status'] == 2) {
                    //отменить счет
                    $payschets->confirmPay([
                        'idpay' => $params['ID'],
                        'result_code' => 2,
                        'trx_id' => 0,
                        'ApprovalCode' => '',
                        'RRN' => '',
                        'message' => $ret['message']
                    ]);
                    return $this->redirect(\yii\helpers\Url::to('/widget/orderok?id='.$params['ID']));

                } else {
                    return $ret;
                }
            } else {
                return ['status' => 0, 'message' => 'Время для оплаты истекло'];
            }

        } else {
            throw new NotFoundHttpException();
        }
    }

    /**
     * Завершение оплаты после 3DS(PCI DSS)
     * @param $id
     * @return string
     * @throws Exception
     * @throws NotFoundHttpException
     */
    public function actionOrderdone($id)
    {
        $payschets = new Payschets();
        //данные счета для оплаты
        $params = $payschets->getSchetData($id, null);

        Yii::warning("WidgetPay done id=".$id);

        if ($params) {

            if ($params['Status'] == 0) {
                //завершить платеж
                $TcbGate = new TcbGate($params['IDPartner'], null, $params['IsCustom']);
                $tcBank = new TCBank($TcbGate);
                $ret = $tcBank->ConfirmXml([
                    'ID' => $params['ID'],
                    'MD' => Yii::$app->request->post('MD'),
                    'PaRes' => Yii::$app->request->post('PaRes')
                ]);
                //ret статус проверить?
            }
            return $this->redirect(\yii\helpers\Url::to('/widget/orderok?id='.$id));

        } else {
            throw new NotFoundHttpException();
        }
    }

    /**
     * Статус оплаты (PCI DSS)
     * @param $id
     * @return string
     * @throws Exception
     * @throws NotFoundHttpException
     */
    public function actionOrderok($id)
    {
        Yii::warning("WidgetPay orderok id=".$id);
        $SesIdPay = Yii::$app->session->get('IdWidgetPay');
        if ($id && $id == $SesIdPay) {
            //завершение оплаты + в колбэк приходит + в планировщике проверяется статус
            sleep(5); //подождать завершения оплаты
            $tcBank = new TCBank();
            $res = $tcBank->confirmPay($id);
            $params = $res['Params'];
            if (!$params) {
                throw new NotFoundHttpException();
            }
            if (in_array($res['status'], [1, 3])) {
                if (!empty($params['SuccessUrl'])) {
                    //перевод на ok
                    return $this->redirect(Payschets::RedirectUrl($params['SuccessUrl'],$params['Extid']));
                } else {
                    return $this->render('paydone', [
                        'message' => 'Оплата прошла успешно.'
                    ]);
                }

            } elseif (in_array($res['status'], [2])) {
                if (!empty($params['FailedUrl'])) {
                    //перевод на fail
                    if (mb_stripos($res['message'], 'Отказ от оплаты') === false) {
                        return $this->redirect(Payschets::RedirectUrl($params['FailedUrl'], $params['Extid']));
                    } else {
                        return $this->redirect(Payschets::RedirectUrl($params['CancelUrl'], $params['Extid']));
                    }
                } else {
                    return $this->render('paycancel', ['message' => $res['message']]);
                }
            } else {
                return $this->render('paywait');
            }
        } else {
            throw new NotFoundHttpException();
        }
    }
}
