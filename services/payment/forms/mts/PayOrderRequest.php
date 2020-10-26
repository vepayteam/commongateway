<?php


namespace app\services\payment\forms\mts;


use yii\base\Model;

class PayOrderRequest extends Model
{
    public $userName;
    public $password;
    public $MDORDER;
    public $PAN;
    public $CVC;
    public $YYYY;
    public $MM;
    public $TEXT;
    public $language;
    public $ip;

    /**
     * @param null $names
     * @param array $except
     * @return array
     */
    public function getAttributes($names = null, $except = [])
    {
        $result = parent::getAttributes($names, $except);
        $result['$PAN'] = $this->PAN;
        $result['$CVC'] = $this->CVC;
        unset($result['PAN']);
        unset($result['CVC']);

        return $result;
    }
}
