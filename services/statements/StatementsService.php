<?php

namespace app\services\statements;

use Closure;
use Yii;
use app\models\mfo\statements\StatementsAccount;
use app\models\mfo\statements\StatementsPlanner;
use app\models\payonline\Partner;
use app\models\queue\ReceiveStatementsJob;

/**
 * Class StatementsService
 *
 * @property Partner $partner
 *
 */
class StatementsService
{
    /**
     *
     * Выписка по счету МФО c ТКБ
     *
     * @param Partner $partner партнёр, для которого получаем список
     * @param int $typeAcc 0 - счет на выдачу, 1 - счет на погашение, 2 - номинальный счет
     * @param int $dateFrom
     * @param int $dateTo
     * @param int $sort одна из констант (SORT_ASC, SORT_DESC, SORT_REGULAR, SORT_NUMERIC и т.д.)
     * @param Closure|null $actionCallback коллбэк-действие при получении выписки
     *
     * @return array|StatementsAccount[]
     */
    public static function GetBankStatements(Partner $partner, int $typeAcc, int $dateFrom, int $dateTo, int $sort = SORT_ASC, ?Closure $actionCallback = null): array
    {
        if ($actionCallback) {
            $actionCallback();
        } else {
            self::handleGetBankStatementsDefaultAction($partner, $typeAcc, $dateFrom,  $dateTo);
        }

        $selectFieldList = [
            'ID', 'IdPartner', 'TypeAccount', 'BnkId', 'NumberPP', 'DatePP', 'DateDoc', 'SummPP', 'SummComis',
            'Description', 'IsCredit', 'Name', 'Inn', 'Account', 'Bic', 'Bank', 'BankAccount',
        ];

        return StatementsAccount
            ::find()->select($selectFieldList)
            ->andWhere(['=', 'IdPartner', $partner->ID])
            ->andWhere(['between', 'DatePP', $dateFrom, $dateTo])
            ->andWhere(['=', 'TypeAccount', $typeAcc])
            ->orderBy(['DatePP' => $sort, 'ID' => $sort])
            ->all();
    }

    /**
     * @param Partner $partner
     * @param int $typeAcc
     * @param int $dateFrom
     * @param int $dateTo
     */
    private static function handleGetBankStatementsDefaultAction(Partner $partner, int $typeAcc, int $dateFrom, int $dateTo): void
    {
        $dates = StatementsPlanner
            ::find()->select(['DateUpdateFrom', 'DateUpdateTo'])
            ->andWhere(['=', 'IdPartner', $partner->ID])
            ->andWhere(['=', 'IdTypeAcc', $typeAcc])
            ->one();
        if (!$dates || $dateFrom < $dates->DateUpdateFrom ||
            ($dateTo > $dates->DateUpdateTo && $dates->DateUpdateTo < time() - 60 * 15)) {
            //обновить выписку (через очередь)
            Yii::$app->queue->push(new ReceiveStatementsJob([
                'IdPartner' => $partner->ID,
                'TypeAcc' => $typeAcc,
                'datefrom' => $dateFrom,
                'dateto' => $dateTo,
            ]));
        }
    }
}
