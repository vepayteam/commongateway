<?php

namespace app\modules\mfo\controllers;

use app\models\api\Reguser;
use app\models\bank\TCBank;
use app\models\kfapi\KfCheckreq;
use app\models\mfo\MfoOutcardReg;
use app\models\mfo\MfoReq;
use app\models\partner\UserLk;
use app\models\payonline\Cards;
use app\models\payonline\User;
use app\models\Payschets;
use app\models\TU;
use app\services\cards\CacheCardService;
use app\services\payment\PaymentService;
use Yii;
use yii\base\InvalidConfigException;
use yii\di\NotInstantiableException;
use yii\filters\auth\HttpBasicAuth;
use yii\helpers\Url;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * Default controller for the `mfo` module
 */
class DefaultController extends Controller
{
    public $layout = '@app/views/layouts/communallayout';

    /**
     * @param $action
     * @return bool
     * @throws BadRequestHttpException
     */
    public function beforeAction($action)
    {
        if ($action->id !== 'addoutcard') {
            $this->enableCsrfValidation = false;
        }

        return parent::beforeAction($action);
    }

    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex()
    {
        $this->layout = '@app/views/layouts/swaggerlayout';
        return $this->render('@app/views/site/apidoc', ['url' => '/mfo/default/swagger']);
    }

    /**
     * Renders the index view for the module
     *
     * @return \yii\console\Response|Response
     * @throws \yii\web\RangeNotSatisfiableHttpException
     */
    public function actionSwagger()
    {
        $content = file_get_contents(Yii::$app->basePath . '/doc/mfo.yaml');
        $content = str_replace('{{current_host}}', Url::base(true), $content);

        return Yii::$app->response->sendContentAsFile($content, 'mfo.yaml', ['inline' => true, 'mimeType' => 'application/yaml']);
    }

    /**
     * Регистрация карт для выплат
     * @param $id
     * @return string
     * @throws NotFoundHttpException
     * @throws \yii\db\Exception
     */
    public function actionOutcard($id)
    {
        $payschets = new Payschets();
        //данные счета для оплаты
        $params = $payschets->getSchetData($id, null);
        if ($params && isset($params['ID']) && $params['Status'] == 0) {
            $cardNumber = null;

            $cacheCardService = new CacheCardService($params['ID']);
            if ($cacheCardService->cardExists()) {
                $cardNumber = $cacheCardService->getCard();
                $cacheCardService->deleteCard();
            }

            return $this->render('outcard', ['IdPay' => $params['ID'], 'cardNumber' => $cardNumber]);
        } else {
            throw new NotFoundHttpException("Идентификатор не найден");
        }
    }

    /**
     * @return array
     * @throws NotFoundHttpException
     * @throws \yii\db\Exception
     */
    public function actionAddoutcard()
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            $IdPay = Yii::$app->request->post('IdPay', '0');
            $card = Yii::$app->request->post('Provparams', '');
            if (isset($card['param']['0'])) {
                $card = $card['param']['0'];
            }

            $mor = new MfoOutcardReg();
            if (preg_match('/^\d{16}|\d{18}$/', $card) && Cards::CheckValidCard($card)) {
                if ($mor->SaveCard($IdPay, 1, $card)) {
                    return ['status' => 1, 'message' => 'Карта добавлена'];
                } else {
                    return ['status' => 0, 'message' => 'Ошибка регистарции карты'];
                }
            }
            return ['status' => 0, 'message' => 'Неверный номер карты'];

        } else {
            throw new NotFoundHttpException("Идентификатор не найден");
        }
    }

    public function actionGetsbpbankreceiver()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $mfo = new MfoReq();
        $mfo->LoadData(Yii::$app->request->getRawBody());

        try {
            $data = $this->getPaymentService()->getSbpBankReceive($mfo->getPartner());
        } catch (NotInstantiableException|InvalidConfigException|\Exception $e) {
            Yii::error([$e->getMessage(), $e->getTrace(), $e->getFile(), $e->getLine()], 'mfo_out');
            return ['status' => 0, 'message' => 'Ошибка запроса'];
        }

        return $this->mapGetsbpbankreceiverResult($data);
    }

    /**
     * @return PaymentService
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\di\NotInstantiableException
     */
    protected function getPaymentService()
    {
        return Yii::$container->get('PaymentService');
    }

    /**
     * @param array $data
     * @return array
     */
    private function mapGetsbpbankreceiverResult(array $data): array
    {
        if (!isset($data['fpsMembers'])) {
            return [];
        }

        $filteredMembers = array_filter($data['fpsMembers'], static function($member) {
            $b2COtherScenarioIndex = array_search('B2COther', array_column($member['scenarios'], 'name'), true);
            // Если в массиве scenarios нет записи с name=B2COther, не включаем в результат
            if ($b2COtherScenarioIndex === false) {
                return false;
            }
            // Если в записи массива scenarios, где name=B2COther, в массиве roles есть значение Receiver, включаем его в результат
            return in_array('Receiver', $member['scenarios'][$b2COtherScenarioIndex]['roles'], true);
        });
        return array_values(array_map(static function($member): array {
            return [
                'name' => $member['bankName'],
                'bankNameRu' => $member['bankNameRu'],
                'bic' => $member['bic'],
            ];
        }, $filteredMembers));
    }
}
