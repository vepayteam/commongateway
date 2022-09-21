<?php

namespace app\modules\partner\services;

use app\models\partner\admin\VyvodParts;
use app\models\PayschetPart;
use app\modules\partner\models\data\PartListItem;
use app\modules\partner\models\forms\BasicPartnerStatisticForm;
use app\modules\partner\models\search\PartListFilter;
use app\services\balance\traits\PartsTrait;
use app\services\payment\models\PaySchet;
use Carbon\Carbon;
use yii\base\Component;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;
use yii\db\Query;

class PartService extends Component
{
    private const DATE_FORMAT = 'd.m.Y';

    /**
     * @param BasicPartnerStatisticForm $form
     * @param PartListFilter $filterForm
     * @param bool $excelMode
     * @return ActiveDataProvider|null
     * @see PartsTrait
     */
    public function search(
        BasicPartnerStatisticForm $form,
        PartListFilter $filterForm,
        bool $excelMode = false
    ): ?ActiveDataProvider
    {
        $from = Carbon::createFromFormat(self::DATE_FORMAT, $form->dateFrom)->timestamp;
        $to = Carbon::createFromFormat(self::DATE_FORMAT, $form->dateTo)->timestamp;

        $query = PayschetPart::find()
            ->alias('payschetPartAlias')
            ->innerJoinWith([
                'paySchet paySchetAlias',
            ])
            ->joinWith([
                'partner partnerAlias',
                'vyvod vyvodAlias' => function (ActiveQuery $query) {
                    $query->onCondition(['vyvodAlias.Status' => VyvodParts::STATUS_COMPLETED]);
                },
            ])
            ->andWhere([
                'paySchetAlias.IdOrg' => $form->partnerId,
                'paySchetAlias.Status' => PaySchet::STATUS_DONE,
            ])
            ->andWhere(['>=', 'paySchetAlias.DateCreate', $from])
            ->andWhere(['<=', 'paySchetAlias.DateCreate', $to]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => $excelMode ? 10000 : 20,
            ],
            'sort' => $excelMode ? false : [
                'attributes' => [
                    'paySchetId' => [
                        'asc' => ['paySchetAlias.ID' => SORT_ASC],
                        'desc' => ['paySchetAlias.ID' => SORT_DESC],
                        'default' => SORT_DESC,
                    ],
                    'partnerName' => [
                        'asc' => ['partnerAlias.Name' => SORT_ASC],
                        'desc' => ['partnerAlias.Name' => SORT_DESC],
                    ],
                    'partAmount' => [
                        'asc' => ['payschetPartAlias.Amount' => SORT_ASC],
                        'desc' => ['payschetPartAlias.Amount' => SORT_DESC],
                    ],
                    'createdAt' => [
                        'asc' => ['paySchetAlias.DateCreate' => SORT_ASC],
                        'desc' => ['paySchetAlias.DateCreate' => SORT_DESC],
                    ],
                    'paySchetAmount' => [
                        'asc' => ['paySchetAlias.SummPay' => SORT_ASC],
                        'desc' => ['paySchetAlias.SummPay' => SORT_DESC],
                    ],
                    'clientCompensation' => [
                        'asc' => ['paySchetAlias.ComissSumm' => SORT_ASC],
                        'desc' => ['paySchetAlias.ComissSumm' => SORT_DESC],
                    ],
                    'partnerCompensation' => [
                        'asc' => ['paySchetAlias.MerchVozn' => SORT_ASC],
                        'desc' => ['paySchetAlias.MerchVozn' => SORT_DESC],
                    ],
                    'bankCompensation' => [
                        'asc' => ['paySchetAlias.BankComis' => SORT_ASC],
                        'desc' => ['paySchetAlias.BankComis' => SORT_DESC],
                    ],
                    'contract' => [
                        'asc' => ['paySchetAlias.Dogovor' => SORT_ASC],
                        'desc' => ['paySchetAlias.Dogovor' => SORT_DESC],
                    ],
                    'fio' => [
                        'asc' => ['paySchetAlias.FIO' => SORT_ASC],
                        'desc' => ['paySchetAlias.FIO' => SORT_DESC],
                    ],
                    'withdrawalPayschetId' => [
                        'asc' => ['vyvodAlias.PayschetId' => SORT_ASC],
                        'desc' => ['vyvodAlias.PayschetId' => SORT_DESC],
                    ],
                    'withdrawalAmount' => [
                        'asc' => ['vyvodAlias.Amount' => SORT_ASC],
                        'desc' => ['vyvodAlias.Amount' => SORT_DESC],
                    ],
                    'withdrawalCreatedAt' => [
                        'asc' => ['vyvodAlias.DateCreate' => SORT_ASC],
                        'desc' => ['vyvodAlias.DateCreate' => SORT_DESC],
                    ],
                ],
            ],
        ]);

        if ($filterForm->validate()) {
            if (!empty($filterForm->createdAt)) {
                $date = Carbon::createFromFormat(self::DATE_FORMAT, $filterForm->createdAt);
                $query
                    ->andFilterWhere(['>=', 'paySchetAlias.DateCreate', $date->startOfDay()->timestamp])
                    ->andFilterWhere(['<=', 'paySchetAlias.DateCreate', $date->endOfDay()->timestamp]);
            }
            $query->andFilterWhere(['paySchetAlias.ID' => $filterForm->paySchetId]);
            $query->andFilterWhere(['like', 'partnerAlias.Name', $filterForm->partnerName]);
            $query->andFilterWhere(['payschetPartAlias.Amount' => $this->convertToFractional($filterForm->partAmount)]);
            $query->andFilterWhere(['like', 'paySchetAlias.Extid', $filterForm->extId]);
            $query->andFilterWhere(['paySchetAlias.SummPay' => $this->convertToFractional($filterForm->paySchetAmount)]);
            $query->andFilterWhere(['paySchetAlias.ComissSumm' => $this->convertToFractional($filterForm->clientCompensation)]);
            $query->andFilterWhere(['paySchetAlias.MerchVozn' => $this->convertToFractional($filterForm->partnerCompensation)]);
            $query->andFilterWhere(['paySchetAlias.BankComis' => $this->convertToFractional($filterForm->bankCompensation)]);
            $query->andFilterWhere(['like', 'paySchetAlias.ErrorInfo', $filterForm->message]);
            $query->andFilterWhere(['like', 'paySchetAlias.CardNum', $filterForm->cardNumber]);
            $query->andFilterWhere(['like', 'paySchetAlias.CardHolder', $filterForm->cardHolder]);
            $query->andFilterWhere(['like', 'paySchetAlias.Dogovor', $filterForm->contract]);
            $query->andFilterWhere(['like', 'paySchetAlias.FIO', $filterForm->fio]);

            $query->andFilterWhere(['vyvodAlias.PayschetId' => $filterForm->withdrawalPayschetId]);
            $query->andFilterWhere(['vyvodAlias.Amount' => $filterForm->withdrawalAmount]);
            if (!empty($filterForm->withdrawalCreatedAt)) {
                $date = Carbon::createFromFormat(self::DATE_FORMAT, $filterForm->withdrawalCreatedAt);
                $query
                    ->andFilterWhere(['>=', 'vyvodAlias.DateCreate', $date->startOfDay()->timestamp])
                    ->andFilterWhere(['<=', 'vyvodAlias.DateCreate', $date->endOfDay()->timestamp]);
            }
        } else {
            $query->andWhere('0=1');
        }

        // exclude refunds
        $query->andWhere([
            'not exists',
            (new Query())
                ->from('pay_schet as refund_pay_schet')
                ->andWhere('refund_pay_schet.RefundSourceId=paySchetAlias.ID')
        ]);

        $models = $dataProvider->getModels();
        foreach ($models as &$model) {
            $model = (new PartListItem())->mapPayschetPart($model);
        }
        $dataProvider->setModels($models);

        return $dataProvider;
    }

    /**
     * Convert main currency units to fractional (dollars to cents).
     *
     * @param float|int $amount
     * @return int|null
     */
    private function convertToFractional($amount): ?int
    {
        return !empty($amount) ? round($amount * 100) : null;
    }
}