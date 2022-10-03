<?php


namespace app\models;


use app\models\partner\admin\VyvodParts;
use app\models\payonline\Partner;
use app\services\payment\models\PaySchet;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * Class PaySchetPart
 * @package app\models
 *
 * @property int $Id
 * @property int $PayschetId
 * @property int $PartnerId
 * @property int $Amount
 * @property int $VyvodId
 *
 * @property-read PaySchet $paySchet {@see PayschetPart::getPaySchet()}
 * @property-read Partner $partner {@see PayschetPart::getPartner()}
 * @property-read VyvodParts $vyvod {@see PayschetPart::getVyvod()}
 */
class PayschetPart extends ActiveRecord
{

    public static function tableName()
    {
        return 'pay_schet_parts';
    }

    public function getPaySchet(): ActiveQuery
    {
        return $this->hasOne(PaySchet::class, ['ID' => 'PayschetId']);
    }

    public function getPartner(): ActiveQuery
    {
        return $this->hasOne(Partner::class, ['ID' => 'PartnerId']);
    }

    public function getVyvod(): ActiveQuery
    {
        return $this->hasOne(VyvodParts::class, ['Id' => 'VyvodId']);
    }
}
