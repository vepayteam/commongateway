<?php


namespace app\commands;


use yii\console\Controller;
use app\services\payment\PaymentService;
use Yii;
use yii\helpers\Json;

class PaymentsController extends Controller
{
    public function actionMassRevert($where, $limit = 0)
    {
        Yii::warning('revertNotRefundErrorRegistrationCard start: ' . Json::encode(func_get_args()));

        $this->getPaymentService()->massRevert($where, $limit);
    }

    public function actionMassRefreshStatus($where, $limit = 0)
    {
        Yii::warning('revertNotRefundErrorRegistrationCard start: ' . Json::encode(func_get_args()));

        $this->getPaymentService()->massRefreshStatus($where, $limit);
    }

    /**
     * @return PaymentService
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\di\NotInstantiableException
     */
    private function getPaymentService()
    {
        return Yii::$container->get('PaymentService');
    }

}
