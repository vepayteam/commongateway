<?php

namespace app\modules\partner\controllers;

use app\models\partner\stat\StatFilter;
use app\models\partner\UserLk;
use app\models\payonline\Partner;
use Yii;

trait SelectPartnerTrait
{
    /**
     * Выбор партнера
     * @param int $IdPart
     * @param bool $all можно всех выбрать
     * @param bool $onlymfo только мфо в списке
     * @param bool $partial
     * @return string
     */
    protected function selectPartner(&$IdPart, $all = false, $onlymfo = false, $partial = false)
    {
        if (UserLk::IsAdmin(Yii::$app->user)) {
            $IdPart = (int)Yii::$app->request->post('IdPartner', 0);
            if (!$IdPart) {
                $IdPart = (int)Yii::$app->request->get('IdPartner', 0);
            }
            if (!$IdPart) {
                $fltr = new StatFilter();
                if ($partial) {
                    return $this->renderPartial('@app/modules/partner/views/selectpartner', [
                        'partners' => $fltr->getPartnersList($onlymfo),
                        'all' => $all,
                        'partial' => $partial
                    ]);

                } else {
                    return $this->render('@app/modules/partner/views/selectpartner', [
                        'partners' => $fltr->getPartnersList($onlymfo),
                        'all' => $all,
                        'partial' => $partial
                    ]);
                }
            }
        } else {
            $IdPart = UserLk::getPartnerId(Yii::$app->user);
        }
        return '';
    }
}