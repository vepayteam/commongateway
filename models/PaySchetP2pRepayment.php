<?php

namespace app\models;

use app\models\payonline\Uslugatovar;
use app\services\cards\models\PanToken;
use app\services\payment\models\PaySchet;
use app\services\payment\models\UslugatovarType;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * P2p repayment data.
 *
 * Used for payments on {@see Uslugatovar} with {@see UslugatovarType::P2P_REPAYMENT}.
 *
 * @property int $paySchetId {@see PaySchet::$ID}.
 * @property int $recipientPanTokenId {@see PanToken::$ID}.
 * @property int|null $presetSenderPanTokenId {@see PanToken::$ID}.
 * @property string|null $presetHash Alfanumeric string of 64 symbols used in payment form URL.
 *
 * @property-read PaySchet $paySchet {@see PaySchetP2pRepayment::getPaySchet()}
 * @property-read PanToken $presetSenderPanToken {@see PaySchetP2pRepayment::getPresetSenderPanToken()}
 */
class PaySchetP2pRepayment extends ActiveRecord
{
    public static function tableName(): string
    {
        return 'pay_schet_p2p_repayment';
    }

    public function getPaySchet(): ActiveQuery
    {
        return $this->hasOne(PaySchet::class, ['ID' => 'paySchetId']);
    }

    public function getPresetSenderPanToken(): ActiveQuery
    {
        return $this->hasOne(PanToken::class, ['ID' => 'presetSenderPanTokenId']);
    }
}