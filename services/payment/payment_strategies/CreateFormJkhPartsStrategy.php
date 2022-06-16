<?php


namespace app\services\payment\payment_strategies;


use app\models\bank\TCBank;
use app\models\kfapi\KfPay;
use app\models\kfapi\KfPayParts;
use app\models\kfapi\KfRequest;
use app\models\TU;
use app\services\LanguageService;
use app\services\payment\payment_strategies\traits\PaymentFormTrait;
use app\services\PaySchetService;
use Yii;
use yii\db\Exception;
use yii\mutex\FileMutex;

class CreateFormJkhPartsStrategy implements IPaymentStrategy
{
    use PaymentFormTrait;

    private $gate;
    /** @var KfRequest */
    private $request;
    /** @var array */
    private $usl;

    private $user;

    public function __construct(KfRequest $kfRequest)
    {
        $this->gate = TCBank::$PARTSGATE;
        $this->request = $kfRequest;
    }

    /**
     * @throws \app\services\payment\exceptions\CreatePayException
     * @throws Exception
     * @throws \yii\base\InvalidConfigException
     */
    public function exec()
    {
        /** @var PaySchetService $paySchetService */
        $paySchetService = \Yii::$app->get(PaySchetService::class);

        $kfPay = new KfPayParts();
        $kfPay->scenario = KfPayParts::SCENARIO_FORM;
        $kfPay->load($this->request->req, '');
        if (!$kfPay->validate()) {
            return ['status' => 0, 'message' => $kfPay->GetError()];
        }

        $usl = $this->getUsl();
        $tcbGate = $this->getTkbGate($this->request->IdPartner);
        if (!$usl || !$tcbGate->IsGate()) {
            return ['status' => 0, 'message' => 'Услуга не найдена'];
        }

        Yii::warning('/merchant/pay id='. $this->request->IdPartner . " sum=".$kfPay->amount . " extid=".$kfPay->extid, 'mfo');

        $mutex = new FileMutex();
        if (!empty($kfPay->extid)) {
            //проверка на повторный запрос
            if (!$mutex->acquire('getPaySchetExt' . $kfPay->extid, 30)) {
                throw new Exception('getPaySchetExt: error lock!');
            }
            $paramsExist = $paySchetService->getPaySchetExt($kfPay->extid, $usl, $this->request->IdPartner);
            if ($paramsExist) {
                if ($kfPay->amount == $paramsExist['sumin']) {
                    return ['status' => 1, 'id' => (int)$paramsExist['IdPay'], 'url' => $kfPay->GetPayForm($paramsExist['IdPay']), 'message' => ''];
                } else {
                    return ['status' => 0, 'id' => 0, 'url' => '', 'message' => 'Нарушение уникальности запроса'];
                }
            }
        }

        $params = $paySchetService->payToMfo($this->getUser(), [$kfPay->descript], $kfPay, $usl, TCBank::$bank, $this->request->IdPartner, 0);
        $this->createPayParts($params);

        /** @var LanguageService $languageService */
        $languageService = Yii::$app->get(LanguageService::class);
        $languageService->saveApiLanguage($params['IdPay'], $kfPay->language);

        if (!empty($kfPay->extid)) {
            $mutex->release('getPaySchetExt' . $kfPay->extid);
        }

        //PCI DSS
        return [
            'status' => 1,
            'id' => (int)$params['IdPay'],
            'url' => $kfPay->GetPayForm($params['IdPay']),
            'message' => ''
        ];
    }

    private function getUsl()
    {
        return Yii::$app->db->createCommand("
            SELECT `ID`
            FROM `uslugatovar`
            WHERE `IDPartner` = :IDMFO AND `IsCustom` = :TYPEUSL AND `IsDeleted` = 0
        ", [':IDMFO' => $this->request->IdPartner, ':TYPEUSL' => TU::$JKHPARTS]
        )->queryScalar();
    }
}
