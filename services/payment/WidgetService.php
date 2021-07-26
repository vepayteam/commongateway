<?php

namespace app\services\payment;

use app\models\kfapi\KfPay;
use app\models\payonline\OrderPay;
use app\models\payonline\Partner;
use app\models\payonline\Uslugatovar;
use app\models\TU;
use app\services\payment\models\PaySchet;
use app\services\PaySchetService;
use Yii;
use yii\db\Exception;

class WidgetService
{
    /**
     * @var int
     */
    private $idPartner;

    public function __construct(int $idPartner)
    {
        $this->idPartner = $idPartner;
    }

    /**
     * @return Partner|null
     */
    public function getPartner(): ?Partner
    {
        return Partner::findOne(['ID' => $this->idPartner, 'IsDeleted' => 0]);
    }

    /**
     * @return Uslugatovar|null
     */
    public function getUslugatovar(): ?Uslugatovar
    {
        return Uslugatovar::findOne(['IDPartner' => $this->idPartner, 'IsCustom' => TU::$ECOM, 'IsDeleted' => 0]);
    }

    /**
     * @param OrderPay $orderPay
     * @param Uslugatovar $uslugatovar
     * @return int|null
     */
    public function createPaySchet(OrderPay $orderPay, Uslugatovar $uslugatovar): ?int
    {
        $formPay = new KfPay();
        $formPay->scenario = KfPay::SCENARIO_FORM;
        $formPay->setAttributes([
            'amount' => $orderPay->SumOrder / 100.00,
            'descript' => $orderPay->Comment,
            'successurl' => $uslugatovar->UrlReturn,
            'failurl' => $uslugatovar->UrlReturnFail
        ]);

        /** @var PaySchetService $paySchetService */
        $paySchetService = Yii::$app->get(PaySchetService::class);

        try {
            $data = $paySchetService->payToMfo(null, [$orderPay->ID, $orderPay->Comment], $formPay, $uslugatovar->ID, 0, $orderPay->IdPartner, 0);
        } catch (Exception $e) {
            Yii::warning('WidgetService db exception: ' . $e->getMessage());
            return null;
        }

        if ($data) {
            $orderPay->IdPaySchet = $data['IdPay'];
            if ($orderPay->ID) {
                $paySchetService->setIdOrder($orderPay->ID, $data);
            }

            return $orderPay->IdPaySchet;
        }

        return null;
    }

    public function isExpired(PaySchet $paySchet): bool
    {
        return ($paySchet->DateCreate + $paySchet->TimeElapsed) <= time();
    }
}
