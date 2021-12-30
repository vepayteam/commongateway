<?php

namespace app\models\partner\callback;

use app\models\payonline\Partner;
use app\services\notifications\models\NotificationPay;

class CallbackFilter
{
    /**
     * Список контрагентов
     * @param $onlymfo boolean
     * @param bool $notehpartner
     * @return Partner[]
     */
    public function getPartnersList($onlymfo = false, $notehpartner = false)
    {
        $query = Partner::find()->where(['IsDeleted' => 0]);
        if ($onlymfo) {
            $query->andWhere(['IsMfo' => $onlymfo]);
        }
        if ($notehpartner) {
            $query->andWhere('ID <> 1');
        }

        return $query->all();
    }

    /**
     * Список http-кодов ответа для фильтрации
     *
     * @return array
     */
    public function getCallbackHTTPResponseStatusList(): array
    {
        $result = array_map(static function(NotificationPay $v): int {
            return $v->HttpCode;
        }, NotificationPay::find()->select(['HttpCode'])->distinct('HttpCode')->cache(30)->all());

        sort($result);

        return $result;
    }
}
