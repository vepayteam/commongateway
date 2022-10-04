<?php

namespace app\modules\partner\services;

use app\models\user\UserIdentification;
use app\modules\partner\models\data\IdentificationListItem;
use app\modules\partner\models\forms\BasicPartnerStatisticForm;
use app\modules\partner\models\search\IdentificationListFilter;
use Carbon\Carbon;
use yii\base\Component;
use yii\data\ActiveDataProvider;

class IdentificationStatisticService extends Component
{
    private const DATE_FORMAT = 'd.m.Y';

    /**
     * @param BasicPartnerStatisticForm $form
     * @param IdentificationListFilter $filterForm
     * @param bool $excelMode
     * @return ActiveDataProvider|null
     * @see IdentService::getIdentStatistic()
     */
    public function search(
        BasicPartnerStatisticForm $form,
        IdentificationListFilter $filterForm,
        bool $excelMode = false
    ): ?ActiveDataProvider
    {
        $from = Carbon::createFromFormat(self::DATE_FORMAT, $form->dateFrom)->timestamp;
        $to = Carbon::createFromFormat(self::DATE_FORMAT, $form->dateTo)->timestamp;

        $query = UserIdentification::find()
            ->andWhere(['IdOrg' => $form->partnerId])
            ->andWhere(['>=', 'DateCreate', $from])
            ->andWhere(['<=', 'DateCreate', $to]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => $excelMode ? 10000 : 20,
            ],
            'sort' => $excelMode ? false : [
                'attributes' => [
                    'id' => [
                        'asc' => ['ID' => SORT_ASC],
                        'desc' => ['ID' => SORT_DESC],
                        'default' => SORT_DESC,
                    ],
                    'createdAt' => [
                        'asc' => ['DateCreate' => SORT_ASC],
                        'desc' => ['DateCreate' => SORT_DESC],
                    ],
                    'transactionNumber' => [
                        'asc' => ['TransNum' => SORT_ASC],
                        'desc' => ['TransNum' => SORT_DESC],
                    ],
                    'firstName' => [
                        'asc' => ['Name' => SORT_ASC],
                        'desc' => ['Name' => SORT_DESC],
                    ],
                    'lastName' => [
                        'asc' => ['Fam' => SORT_ASC],
                        'desc' => ['Fam' => SORT_DESC],
                    ],
                    'middleName' => [
                        'asc' => ['Otch' => SORT_ASC],
                        'desc' => ['Otch' => SORT_DESC],
                    ],
                    'inn' => [
                        'asc' => ['Inn' => SORT_ASC],
                        'desc' => ['Inn' => SORT_DESC],
                    ],
                    'snils' => [
                        'asc' => ['Snils' => SORT_ASC],
                        'desc' => ['Snils' => SORT_DESC],
                    ],
                    'passportSeries' => [
                        'asc' => ['PaspSer' => SORT_ASC],
                        'desc' => ['PaspSer' => SORT_DESC],
                    ],
                    'passportNumber' => [
                        'asc' => ['PaspNum' => SORT_ASC],
                        'desc' => ['PaspNum' => SORT_DESC],
                    ],
                    'passportDepartmentCode' => [
                        'asc' => ['PaspPodr' => SORT_ASC],
                        'desc' => ['PaspPodr' => SORT_DESC],
                    ],
                    'passportIssueDate' => [
                        'asc' => ['PaspDate' => SORT_ASC],
                        'desc' => ['PaspDate' => SORT_DESC],
                    ],
                    'passportIssuedBy' => [
                        'asc' => ['PaspVidan' => SORT_ASC],
                        'desc' => ['PaspVidan' => SORT_DESC],
                    ],
                ],
            ],
        ]);

        if ($filterForm->validate()) {
            if (!empty($filterForm->createdAt)) {
                $date = Carbon::createFromFormat(self::DATE_FORMAT, $filterForm->createdAt);
                $query
                    ->andFilterWhere(['>=', 'DateCreate', $date->startOfDay()->timestamp])
                    ->andFilterWhere(['<=', 'DateCreate', $date->endOfDay()->timestamp]);
            }
            if (!empty($filterForm->passportIssueDate)) {
                $date = Carbon::createFromFormat(self::DATE_FORMAT, $filterForm->passportIssueDate);
                $query
                    ->andFilterWhere(['>=', 'PaspDate', $date->startOfDay()->timestamp])
                    ->andFilterWhere(['<=', 'PaspDate', $date->endOfDay()->timestamp]);
            }
            $query->andFilterWhere(['ID' => $filterForm->id]);
            $query->andFilterWhere(['like', 'TransNum', $filterForm->transactionNumber]);
            $query->andFilterWhere(['like', 'Name', $filterForm->firstName]);
            $query->andFilterWhere(['like', 'Fam', $filterForm->lastName]);
            $query->andFilterWhere(['like', 'Otch', $filterForm->middleName]);
            $query->andFilterWhere(['like', 'Inn', $filterForm->inn]);
            $query->andFilterWhere(['like', 'Snils', $filterForm->snils]);
            $query->andFilterWhere(['like', 'PaspSer', $filterForm->passportSeries]);
            $query->andFilterWhere(['like', 'PaspNum', $filterForm->passportNumber]);
            $query->andFilterWhere(['like', 'PaspPodr', $filterForm->passportDepartmentCode]);
            $query->andFilterWhere(['like', 'PaspVidan', $filterForm->passportIssuedBy]);
        } else {
            $query->andWhere('0=1');
        }

        $models = $dataProvider->getModels();
        foreach ($models as &$model) {
            $model = (new IdentificationListItem())->mapUserIdentification($model);
        }
        $dataProvider->setModels($models);

        return $dataProvider;
    }
}