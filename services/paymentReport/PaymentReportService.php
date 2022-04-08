<?php

namespace app\services\paymentReport;

use app\models\partner\stat\PayShetStat;
use app\models\partner\UserLk;
use app\services\payment\models\PaySchet;
use app\services\paymentReport\entities\PaymentReportEntity;
use app\services\paymentReport\forms\PaymentReportForm;
use yii\base\Component;
use yii\db\Query;

class PaymentReportService extends Component
{
    /**
     * @param bool $isAdmin
     * @param PayShetStat $payShetStat
     * @return array
     */
    public function getLegacyReportEntities(bool $isAdmin, PayShetStat $payShetStat): array
    {
        $form = PaymentReportForm::fromPayShetStat($payShetStat);
        $reportEntities = $this->getReportEntities($isAdmin, $form);

        $legacyEntities = [];

        /** @var PaymentReportEntity $value */
        foreach ($reportEntities as $value) {
            $legacyEntities[] = [
                'IdUsluga' => $value->paymentReportGroup->serviceId,
                'IsCustom' => $value->paymentReportGroup->serviceType,
                'ProvVoznagPC' => $value->paymentReportGroup->partnerCommission,
                'ProvVoznagMin' => $value->paymentReportGroup->partnerMinimalFee,
                'ProvComisPC' => $value->paymentReportGroup->bankCommission,
                'ProvComisMin' => $value->paymentReportGroup->bankMinimalFee,
                'bankName' => $value->paymentReportGroup->bankName,
                'NameUsluga' => $value->paymentReportGroup->serviceName,
                'SummPay' => $value->totalPaymentAmount,
                'ComissSumm' => $value->totalClientCommission,
                'MerchVozn' => $value->totalMerchantReward,
                'BankComis' => $value->totalBankCommission,
                'CntPays' => $value->paymentCount,
                'VoznagSumm' => $value->totalRewardAmount,
            ];
        }

        return $legacyEntities;
    }

    /**
     * @param bool $isAdmin
     * @param PaymentReportForm $form
     * @return array
     */
    public function getReportEntities(bool $isAdmin, PaymentReportForm $form): array
    {
        $rawEntities = $this->loadQuery($isAdmin, $form);

        $totalEntities = [];
        foreach ($rawEntities as $rawEntity) {
            $entity = PaymentReportEntity::fromQuery($rawEntity);
            $keyEntity = $entity->paymentReportGroup->getKey();

            if (!isset($totalEntities[$keyEntity])) {
                $totalEntities[$keyEntity] = PaymentReportEntity::newEmptyEntity($entity->paymentReportGroup);
            }

            /**
             * Для платежей со статусом done добавляем к результату суммы
             * Для остальных платежей со статусами reverse/refund вычитаем из итога суммы
             */
            if ((int)$entity->status === PaySchet::STATUS_DONE) {
                $totalEntities[$keyEntity]->addEntity($entity);
            } else {
                $totalEntities[$keyEntity]->subEntity($entity);
            }
        }

        return array_values($totalEntities);
    }

    /**
     * @param bool $isAdmin
     * @param PaymentReportForm $form
     * @return array
     */
    private function loadQuery(bool $isAdmin, PaymentReportForm $form): array
    {
        $partnerId = $isAdmin ? $form->partnerId : UserLk::getPartnerId(\Yii::$app->user);

        $query = (new Query())
            ->select([
                '`ps`.`IdUsluga` as serviceId',
                '`ut`.`IsCustom` as serviceType',
                '`ut`.`NameUsluga` as serviceName',
                '`ut`.`ProvVoznagPC` as partnerCommission',
                '`ut`.`ProvVoznagMin` as partnerMinimalFee',
                '`ut`.`ProvComisPC` as bankCommission',
                '`ut`.`ProvComisMin` as bankMinimalFee',
                '`b`.`Name` as bankName',
                '`ps`.`Status` as status',
                'SUM(`ps`.`SummPay`) AS totalPaymentAmount',
                'SUM(`ps`.`ComissSumm`) AS totalClientCommission',
                'SUM(`ps`.`BankComis`) AS totalBankCommission',
                'SUM(`ps`.`MerchVozn`) AS totalMerchantReward',
                'COUNT(*) AS paymentCount',
            ])
            ->from('`pay_schet` AS ps')
            ->leftJoin('`uslugatovar` AS ut', 'ps.IdUsluga = ut.ID')
            ->leftJoin('`banks` AS b', 'ps.Bank = b.ID')
            ->andWhere(['between', 'ps.DateCreate', strtotime($form->dateFrom . ":00"), strtotime($form->dateTo . ":59")])
            ->andWhere(['in', 'ps.Status', [PaySchet::STATUS_DONE, PaySchet::STATUS_CANCEL, PaySchet::STATUS_REFUND_DONE]])
            ->groupBy([
                '`ps`.`IdUsluga`',
                '`ut`.`IsCustom`',
                '`ut`.NameUsluga',
                '`ut`.`ProvVoznagPC`',
                '`ut`.`ProvVoznagMin`',
                '`ut`.`ProvComisPC`',
                '`ut`.`ProvComisMin`',
                '`b`.`Name`',
                '`ps`.Status',
            ])
            ->orderBy('ps.Bank');

        $query->andFilterWhere(['ut.IDPartner' => $partnerId > 0 ? $partnerId : null]);
        $query->andFilterWhere(['ut.ID' => $form->serviceIds]);
        $query->andFilterWhere(['ut.IsCustom' => $form->serviceTypes]);
        $query->andFilterWhere(['ps.Bank' => $form->bankIds]);

        return $query->all();
    }
}
