<?php

namespace app\models\mfo;

use app\models\payonline\Uslugatovar;
use app\models\TU;
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

    public function rules()
    {
        return [
            [['IdPartner'], 'integer'],
            [['url', 'UrlReturn', 'UrlReturnFail', 'UrlCheckReq', 'UrlReturnCancel'], 'url'],
            [['url', 'UrlReturn', 'UrlReturnFail', 'UrlCheckReq', 'UrlReturnCancel'], 'string', 'max' => 300],
            ['key', 'string', 'max' => 20]
        ];
    }

    /**
     * Прочитать данные из услуги
     */
    public function ReadUrl()
    {
        $usl = null;
        $tp = [TU::$POGASHECOM, TU::$ECOM, TU::$JKH];
        foreach ($tp as $uslType) {
            $usl = Uslugatovar::findOne(['IsCustom' => $uslType, 'IDPartner' => $this->IdPartner, 'IsDeleted' => 0]);
            if ($usl) {
                break;
            }
        }
        if ($usl) {
            $this->url = $usl->UrlInform;
            $this->key = $usl->KeyInform;
            $this->UrlReturn = $usl->UrlReturn;
            $this->UrlReturnFail = $usl->UrlReturnFail;
            $this->UrlReturnCancel = $usl->UrlReturnCancel;
            $this->UrlCheckReq = $usl->UrlCheckReq;
        }
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