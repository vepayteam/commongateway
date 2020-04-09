<?php

namespace app\models\payonline;

use Yii;
use yii\filters\RateLimitInterface;

/**
 * This is the model class for table "user".
 *
 * @property string $ID
 * @property string $Login
 * @property string $Password
 * @property int $ExtOrg
 * @property string $Pinpay
 * @property int $BonusBalance
 * @property string $ExtCustomerIDP
 * @property string $Fam
 * @property string $Name
 * @property string $Otch
 * @property string $Inn
 * @property string $Snils
 * @property string $Email
 * @property string $TempEmail
 * @property string $VerificCode
 * @property string $Phone
 * @property string $DateRegister
 * @property string $IMEI
 * @property integer $UserDeviceType
 * @property integer $SendUvedolmen
 * @property integer $SendPush
 * @property integer $SendInSchets
 * @property integer $SendInfoOplata
 * @property integer $SendReclPartner
 * @property integer $IsBonusCopy
 * @property integer $IsUsePassw
 * @property integer $IsUsePinpay
 * @property integer $IsDeleted
 */
class User extends \yii\db\ActiveRecord implements RateLimitInterface
{
    public static $UserDeviceType_str  = [0 => '', 1 => 'web', 2 => 'android', 3 => 'iphone'];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['DateRegister'], 'required'],
            [['DateRegister', 'UserDeviceType', 'IsDeleted'], 'integer'],
            [['Login'], 'string', 'max' => 50],
            [['Password'], 'string', 'max' => 512],
            [['ExtCustomerIDP', 'VerificCode'], 'string', 'max' => 128],
            [['Fam', 'Name', 'Otch', 'Email', 'TempEmail'], 'string', 'max' => 100],
            [['Phone'], 'string', 'max' => 20],
            [['IMEI'], 'string', 'max' => 36]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'ID' => 'ID',
            'Login' => 'login',
            'Password' => 'parol sha',
            'ExtCustomerIDP' => 'vneshniii id klienta 64 simvola',
            'Fam' => 'familia',
            'Name' => 'imia',
            'Otch' => 'otchestvo',
            'Email' => 'pochta / mobile identificator',
            'TempEmail' => 'vremennyii email',
            'VerificCode' => 'kod podtverjdenia',
            'Phone' => 'telefon',
            'DateRegister' => 'data registracii',
            'IMEI' => 'IMEI nomer telefona / UUID',
            'UserDeviceType' => 'tip ustroistva: 0 - n/a 1 - web 2 - android 3 - iphone',
            'IsDeleted' => '0 - activen 1 - udalen',
        ];
    }

    public function getFio()
    {
        return $this->Fam . ' ' . $this->Name . ' ' .$this->Otch;
    }

    /**
     * @param Provparams $ProvParams
     * @return boolean
     */
    public function getSend($ProvParams)
    {
        $issend = false;
        if ($ProvParams) {
            $reestrs = new ReestrSchets();
            $shabl = &$reestrs->getShablon($ProvParams);
            $issend = $shabl->IsAllowSendLink;
        }

        return $issend;
    }

    public function allowUpdFields()
    {
        return [
            "Fam",
            "Name",
            "Otch",
            "Phone",
            "Email",
            "SendUvedolmen",
            "SendPush",
            "SendInSchets",
            "SendInfoOplata",
            "SendReclPartner",
            "IsBonusCopy",
            "IsUsePassw",
            "IsUsePinpay"
        ];
    }

    /**
     * Returns the maximum number of allowed requests and the window size.
     * @param \yii\web\Request $request the current request
     * @param \yii\base\Action $action the action to be executed
     * @return array an array of two elements. The first element is the maximum number of allowed requests,
     * and the second element is the size of the window in seconds.
     */
    public function getRateLimit($request, $action)
    {
        return [30, 30]; //не более 30 запросов в течение 30 секунд
    }

    /**
     * Loads the number of allowed requests and the corresponding timestamp from a persistent storage.
     * @param \yii\web\Request $request the current request
     * @param \yii\base\Action $action the action to be executed
     * @return array an array of two elements. The first element is the number of allowed requests,
     * and the second element is the corresponding UNIX timestamp.
     */
    public function loadAllowance($request, $action)
    {
        $rate = Yii::$app->cache->get($this->ID."rate");
        if ($rate === false) {
            $count = 0;
            $time = time();
        } else {
            $count = $rate['count'];
            $time = $rate['time'];
        }
        return [$count, $time];
    }

    /**
     * Saves the number of allowed requests and the corresponding timestamp to a persistent storage.
     * @param \yii\web\Request $request the current request
     * @param \yii\base\Action $action the action to be executed
     * @param int $allowance the number of allowed requests remaining.
     * @param int $timestamp the current timestamp.
     */
    public function saveAllowance($request, $action, $allowance, $timestamp)
    {
        Yii::$app->cache->set($this->ID."rate", ["count" => $allowance, "time" => $timestamp]);
    }
}
