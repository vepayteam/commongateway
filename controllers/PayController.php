<?php

namespace app\controllers;

use app\models\antifraud\AntiFraud;
use app\models\antifraud\tables\AFFingerPrit;
use app\models\bank\TCBank;
use app\models\bank\TcbGate;
use app\models\crypt\Tokenizer;
use app\models\mfo\MfoTestError;
use app\models\payonline\Cards;
use app\models\payonline\PayForm;
use app\models\Payschets;
use app\models\TU;
use Yii;
use yii\db\Exception;
use yii\helpers\VarDumper;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class PayController extends Controller
{
    public $layout = 'paylayout';

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
     * Форма оплаты своя (PCI DSS)
     * @param $id
     * @return string
     * @throws NotFoundHttpException
     * @throws \yii\db\Exception
     */
    public function actionForm($id)
    {
        Yii::warning("PayForm open id=".$id);
        $payschets = new Payschets();
        //данные счета для оплаты
        $params = $payschets->getSchetData($id, null);
        $payform = new PayForm();
        if ($params && TU::IsInPay($params['IsCustom'])) {
            if ($params['Status'] == 0 && $params['UserClickPay'] == 0 &&
                $params['DateCreate'] + $params['TimeElapsed'] > time()
            ) {
                $payschets->SetIpAddress($params['ID']);

                //разрешить открытие во фрейме на сайте мерчанта
                $csp = "default-src 'self' 'unsafe-inline' https://mc.yandex.ru; img-src 'self' data: https://mc.yandex.ru; connect-src 'self' https://mc.yandex.ru;";
                if (!empty($params['URLSite'])) {
                    $csp .= ' frame-src ' . $params['URLSite'].';';
                }
                Yii::$app->response->headers->add('Content-Security-Policy', $csp);
                return $this->render('formpay', ['params' => $params, 'payform' => $payform]);

            } else {
                return $this->redirect(\yii\helpers\Url::to('/pay/orderok?id='.$id));
            }
        } else {
            throw new NotFoundHttpException("Счет для оплаты не найден");
        }
    }

    /**
     * Форма оплаты своя (PCI DSS)
     * @return array|Response
     * @throws NotFoundHttpException
     * @throws \yii\db\Exception
     */
    public function actionCreatepay()
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $payform = new PayForm();

            $payform->load(Yii::$app->request->post(), 'PayForm');
            if (!$payform->validate()) {
                return ['status' => 0, 'message' => $payform->GetError()];
            }

            Yii::warning("PayForm create id=" . $payform->IdPay);
            //данные счета для оплаты
            $payschets = new Payschets();
            $params = $payschets->getSchetData($payform->IdPay, null);
            if ($params && $params['DateCreate'] + $params['TimeElapsed'] > time()) {

                Yii::$app->session->set('IdPay', $params['ID']);

                if ($params['Status'] != 0 || $params['UserClickPay'] != 0 || !TU::IsInPay($params['IsCustom'])) {
                    //нельзя оплачивать
                    return $this->redirect(\yii\helpers\Url::to('/pay/orderok?id='.$params['ID']));
                }

                if (!$this->antifraud_validated($payform, Yii::$app->request->post('user_hash'))) {
                    //действие если антифрод проверку не прошел.
                    //пока оставить пустым т.к. собирается статистика.
                }

                $params['card']['number'] = $payform->CardNumber;
                $params['card']['holder'] = $payform->CardHolder;
                $params['card']['year'] = $payform->CardYear;
                $params['card']['month'] = $payform->CardMonth;
                $params['card']['cvc'] = $payform->CardCVC;

                //занести данные карты
                $payschets->SetCardPay($params['ID'], $params['card']);

                //$params['Bank'] == 2
                $gate = TCBank::$ECOMGATE;
                if ($params['IsCustom'] == TU::$JKH) {
                    $gate = TCBank::$JKHGATE;
                } elseif ($params['IsCustom'] == TU::$POGASHATF) {
                    $gate = TCBank::$AFTGATE;
                    if (Cards::GetTypeCard($payform->CardNumber) == 6) {
                        //карты маэстро только по еком надо
                        $gate = TCBank::$ECOMGATE;
                        $payschets->ChangeUsluga($params['ID'], $params['IDPartner'], TU::$POGASHECOM);
                    }
                }

                if ($params['IdUsluga'] == 1) {
                    //регистрация карты
                    $TcbGate = new TcbGate($params['IdOrg'], TCBank::$AUTOPAYGATE);
                } else {
                    $TcbGate = new TcbGate($params['IDPartner'], $gate);
                }
                $tcBank = new TCBank($TcbGate);
                $ret = $tcBank->PayXml($params);

                if ($ret['status'] == 1) {
                    $payschets->SetStartPay($params['ID'], $ret['transac'], $payform->Email);

                    if ($params['IdUsluga'] == 1) {
                        //регистрация карты
                        $tokenizer = new Tokenizer();
                        if (($IdPan = $tokenizer->CheckExistToken($payform->CardNumber,$payform->CardMonth.$payform->CardYear)) == 0) {
                            $IdPan = $tokenizer->CreateToken($payform->CardNumber, $payform->CardMonth.$payform->CardYear, $payform->CardHolder);
                        }

                        if ($IdPan) {
                            //сохранить карту
                            $payschets->SaveCardPan($params['IdUser'], $params['card'], $IdPan);
                        }
                    }

                    //отправить запрос адреса формы 3ds
                    return [
                        'status' => 1,
                        'url' => $ret['url'],
                        'pa' => $ret['pa'],
                        'md' => $ret['md'],
                        'termurl' => $payform->GetRetUrl($params['ID']),
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
                    return $this->redirect(\yii\helpers\Url::to('/pay/orderok?id='.$params['ID']));

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
     * Отказаться от оплтаты
     * @return array
     * @throws Exception
     * @throws NotFoundHttpException
     */
    public function actionDeclinepay()
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            $payschets = new Payschets();
            $params = $payschets->getSchetData(Yii::$app->request->post('ID'),null);
            if ($params && $params['Status'] == 0 && $params['UserClickPay'] == 0 && TU::IsInPay($params['IsCustom'])) {
                Yii::$app->session->set('IdPay', $params['ID']);
                //отменить счет
                $payschets->confirmPay([
                    'idpay' => $params['ID'],
                    'result_code' => 2,
                    'trx_id' => 0,
                    'ApprovalCode' => '',
                    'RRN' => '',
                    'message' => 'Отказ от оплаты'
                ]);
                return ['status' => 1];
            }
            return ['status' => 0];
        } else {
            throw new NotFoundHttpException("Счет для оплаты не найден");
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

        Yii::warning("PayForm done id=".$id);

        if ($params) {
            $md = Yii::$app->request->post('MD', '');
            if ($params['Status'] == 0 && !empty($md)) {
                //завершить платеж
                if ($params['IdUsluga'] == 1) {
                    //регистрация карты
                    $TcbGate = new TcbGate($params['IdOrg'], TCBank::$AUTOPAYGATE);
                } else {
                    $TcbGate = new TcbGate($params['IDPartner'], null, $params['IsCustom']);
                }

                $tcBank = new TCBank($TcbGate);
                $ret = $tcBank->ConfirmXml([
                    'ID' => $params['ID'],
                    'MD' => $md,
                    'PaRes' => Yii::$app->request->post('PaRes')
                ]);
                //ret статус проверить?
            }
            // TODO:
            if($params['IdUsluga'] == 1 && $params['ExtOrg'] == '3') {
                return $this->redirect("https://cashtoyou.ru/registration/third/");
            } elseif ($params['IdUsluga'] == 1 && $params['IDPartner'] == '8') {
                return $this->redirect("https://oneclickmoney.ru/registration/third/");
            } else {
                return $this->redirect(\yii\helpers\Url::to('/pay/orderok?id='.$params['ID']));
            }

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
        Yii::warning("PayForm orderok id=".$id);
        $SesIdPay = Yii::$app->session->get('IdPay');
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
                if (!empty($params['FailedUrl']) && (mb_stripos($res['message'], 'Отказ от оплаты') === false || empty($params['CancelUrl']))) {
                    //перевод на fail
                    return $this->redirect(Payschets::RedirectUrl($params['FailedUrl'], $params['Extid']));
                } elseif (!empty($params['CancelUrl'])) {
                    //перевод на cancel
                    return $this->redirect(Payschets::RedirectUrl($params['CancelUrl'], $params['Extid']));
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

//    public function actionRegisterTracking(){
//        Yii::$app->response->format = Response::FORMAT_JSON;
//        if ()
//        new FingerPrint();
//    }

    private function antifraud_validated(PayForm $form, $user_hash): bool
    {
        /**@var AFFingerPrit $finger_print */
        try{
            $antifraud = new AntiFraud($form->IdPay);
            $validated = $antifraud->validate($user_hash, $form->CardNumber);
            return $validated;
        } catch (\Throwable $e) {
            return true;
        }
    }

}
