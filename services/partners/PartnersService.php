<?php


namespace app\services\partners;


use app\models\payonline\Partner;
use app\services\partners\models\PartnerOption;
use yii\web\BadRequestHttpException;

class PartnersService
{

    /**
     * @param $postData
     * @return bool
     * @throws BadRequestHttpException
     */
    public function saveOptions($postData)
    {
        $partner = Partner::findOne(['ID' => (int)$postData['PartnerId']]);
        if(!$partner) {
            throw new BadRequestHttpException('Партнер не найден');
        }

        foreach ($postData as $k => $v) {
            if(!array_key_exists($k, PartnerOption::LIST)) {
                continue;
            }

            $partnerOption = $partner->getOptions()->where(['Name' => $k])->one();
            if(!$partnerOption) {
                $partnerOption = new PartnerOption();
                $partnerOption->Name = $k;
                $partnerOption->link('partner', $partner);
            }
            $partnerOption->Value = htmlspecialchars($v);
            $partnerOption->save();
        }

        return true;
    }

}
