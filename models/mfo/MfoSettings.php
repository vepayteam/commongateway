<?php

namespace app\models\mfo;

use app\models\partner\UserLk;
use app\models\payonline\Uslugatovar;
use app\models\TU;
use Yii;
use yii\base\Model;

class MfoSettings extends Model
{
    public $IdPartner;
    public $url = '';
    public $key = '';
    public $UrlReturn = '';
    public $UrlReturnFail = '';
    public $UrlReturnCancel = '';
    public $UrlCheckReq = '';

    const CALLBACK_NAME_URLS = ['UrlReturn', 'UrlReturnFail', 'UrlReturnCancel'];

    public function rules()
    {
        return [
            [['IdPartner'], 'integer'],
            [['url', 'UrlReturn', 'UrlReturnFail', 'UrlCheckReq', 'UrlReturnCancel'], 'url'],
            [['url', 'UrlReturn', 'UrlReturnFail', 'UrlCheckReq', 'UrlReturnCancel'], 'string', 'max' => 300],
            ['key', 'string', 'max' => 20],
            [self::CALLBACK_NAME_URLS, 'validateOnlyAdminCanChange'],
        ];
    }


    public function validateOnlyAdminCanChange()
    {
        $isAdmin = UserLk::IsAdmin(Yii::$app->user);
        if(!$isAdmin) {
            $usl = $this->getUslugatovarByUrlReturns();
            foreach (self::CALLBACK_NAME_URLS as $callbackUrl) {
                if($usl->$callbackUrl != $this->$callbackUrl) {
                    $this->addError($callbackUrl, 'Только администратор может изменять это поле');
                }
            }
        }
    }

    /**
     * Прочитать данные из услуги
     */
    public function ReadUrl()
    {
        $usl = $this->getUslugatovarByUrlReturns();

        if(!$usl) {
            return false;
        }

        $this->url = $usl->UrlInform;
        $this->key = $usl->KeyInform;
        $this->UrlReturn = $usl->UrlReturn;
        $this->UrlReturnFail = $usl->UrlReturnFail;
        $this->UrlReturnCancel = $usl->UrlReturnCancel;
        $this->UrlCheckReq = $usl->UrlCheckReq;
        return true;
    }

    private function getUslugatovarByUrlReturns()
    {
        $tp = [TU::$POGASHECOM, TU::$ECOM, TU::$JKH];
        foreach ($tp as $uslType) {
            $usl = Uslugatovar::findOne(['IsCustom' => $uslType, 'IDPartner' => $this->IdPartner, 'IsDeleted' => 0]);
            if ($usl) {
                break;
            }
        }

        return $usl ?? null;
    }

    /**
     * Сохранить
     * @return int
     */
    public function Save()
    {
        $usl = Uslugatovar::findOne(['IsCustom' => TU::$TOSCHET, 'IDPartner' => $this->IdPartner, 'IsDeleted' => 0]);
        if ($usl) {
            $usl->UrlInform = $this->url;
            $usl->KeyInform = $this->key;
            $usl->save(false);
        }

        $usl = Uslugatovar::findOne(['IsCustom' => TU::$TOCARD, 'IDPartner' => $this->IdPartner, 'IsDeleted' => 0]);
        if ($usl) {
            $usl->UrlInform = $this->url;
            $usl->KeyInform = $this->key;
            $usl->save(false);
        }

        $usl = Uslugatovar::findOne(['IsCustom' => TU::$POGASHATF, 'IDPartner' => $this->IdPartner, 'IsDeleted' => 0]);
        if ($usl) {
            $usl->UrlInform = $this->url;
            $usl->KeyInform = $this->key;
            $usl->UrlReturn = $this->UrlReturn;
            $usl->UrlReturnFail = $this->UrlReturnFail;
            $usl->UrlReturnCancel = $this->UrlReturnCancel;
            $usl->UrlCheckReq = $this->UrlCheckReq;
            $usl->save(false);
        }

        $usl = Uslugatovar::findOne(['IsCustom' => TU::$AVTOPLATATF, 'IDPartner' => $this->IdPartner, 'IsDeleted' => 0]);
        if ($usl) {
            $usl->UrlInform = $this->url;
            $usl->KeyInform = $this->key;
            $usl->save(false);
        }

        $usl = Uslugatovar::findOne(['IsCustom' => TU::$POGASHECOM, 'IDPartner' => $this->IdPartner, 'IsDeleted' => 0]);
        if ($usl) {
            $usl->UrlInform = $this->url;
            $usl->KeyInform = $this->key;
            $usl->UrlReturn = $this->UrlReturn;
            $usl->UrlReturnFail = $this->UrlReturnFail;
            $usl->UrlReturnCancel = $this->UrlReturnCancel;
            $usl->UrlCheckReq = $this->UrlCheckReq;
            $usl->save(false);
        }

        $usl = Uslugatovar::findOne(['IsCustom' => TU::$AVTOPLATECOM, 'IDPartner' => $this->IdPartner, 'IsDeleted' => 0]);
        if ($usl) {
            $usl->UrlInform = $this->url;
            $usl->KeyInform = $this->key;
            $usl->save(false);
        }

        //merchant
        $usl = Uslugatovar::findOne(['IsCustom' => TU::$JKH, 'IDPartner' => $this->IdPartner, 'IsDeleted' => 0]);
        if ($usl) {
            $usl->UrlInform = $this->url;
            $usl->KeyInform = $this->key;
            $usl->UrlReturn = $this->UrlReturn;
            $usl->UrlReturnFail = $this->UrlReturnFail;
            $usl->UrlReturnCancel = $this->UrlReturnCancel;
            $usl->save(false);
        }

        $usl = Uslugatovar::findOne(['IsCustom' => TU::$ECOM, 'IDPartner' => $this->IdPartner, 'IsDeleted' => 0]);
        if ($usl) {
            $usl->UrlInform = $this->url;
            $usl->KeyInform = $this->key;
            $usl->UrlReturn = $this->UrlReturn;
            $usl->UrlReturnFail = $this->UrlReturnFail;
            $usl->UrlReturnCancel = $this->UrlReturnCancel;
            $usl->save(false);
        }

        return 1;
    }
}
