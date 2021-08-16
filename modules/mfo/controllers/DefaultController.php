<?php

namespace app\modules\mfo\controllers;

use app\models\api\Reguser;
use app\models\bank\TCBank;
use app\models\kfapi\KfCheckreq;
use app\models\mfo\MfoOutcardReg;
use app\models\partner\UserLk;
use app\models\payonline\Cards;
use app\models\payonline\User;
use app\models\Payschets;
use app\models\TU;
use app\services\payment\PaymentService;
use Yii;
use yii\base\InvalidConfigException;
use yii\di\NotInstantiableException;
use yii\filters\auth\HttpBasicAuth;
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
        $this->enableCsrfValidation = false;
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
     * @return string
     */
    public function actionSwagger()
    {
        return Yii::$app->response->sendFile(Yii::$app->basePath . '/doc/mfo.yaml', '', ['inline' => true, 'mimeType' => 'application/yaml']);
    }

    /**
     * Регистрация карт для выплат
     * @param $id
     * @param string|null $cardNumber
     * @return string
     * @throws NotFoundHttpException
     * @throws \yii\db\Exception
     */
    public function actionOutcard($id, $cardNumber = null)
    {
        $payschets = new Payschets();
        //данные счета для оплаты
        $params = $payschets->getSchetData($id, null);
        $user = User::find()->where(['ID' => $params['IdUser'], 'IsDeleted' => 0])->one();
        if ($params && isset($params['ID']) && $params['Status'] == 0) {
            return $this->render('outcard', ['user' => $user, 'IdPay' => $params['ID'], 'cardNumber' => $cardNumber]);
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

            $extuser = Yii::$app->request->post('extuser', '');
            $extpw = Yii::$app->request->post('extpw', '');
            $extorg = Yii::$app->request->post('extorg', 0);

            $reguser = new Reguser();
            $user = $reguser->findUser('0', $extuser, $extpw, $extorg, 0);

            $mor = new MfoOutcardReg();
            if (preg_match('/^\d{16}|\d{18}$/', $card) && Cards::CheckValidCard($card)) {
                if ($mor->SaveCard($IdPay, 1, $card, $user, $extorg)) {
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
        try {
            $data = $this->getPaymentService()->getSbpBankReceive();
        } catch (NotInstantiableException | InvalidConfigException | \Exception $e) {
            return ['status' => 0, 'message' => $e->getMessage()];
        }
        Yii::$app->response->format = Response::FORMAT_JSON;

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
