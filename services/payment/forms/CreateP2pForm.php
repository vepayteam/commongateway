<?php


namespace app\services\payment\forms;


use app\models\payonline\Partner;
use app\models\traits\ValidateFormTrait;
use app\services\payment\models\Currency;
use Yii;
use yii\base\Model;

class CreateP2pForm extends Model
{
    use ValidateFormTrait;

    /** @var Partner */
    public $partner;

    public $currency = Currency::MAIN_CURRENCY;
    public $timeout = 30;
    public $document_id = '';
    public $fullname = '';
    public $extid = '';
    public $descript = '';
    public $successurl = '';
    public $failurl = '';
    public $cancelurl = '';
    public $postbackurl = '';
    public $postbackurl_v2 = '';

    public function rules()
    {
        return [
            [['extid'], 'string', 'max' => 40],
            [['document_id'], 'string'],
            [['fullname'], 'string', 'max' => 80],
            [['postbackurl', 'postbackurl_v2'], 'url'],
            [['postbackurl', 'postbackurl_v2'], 'string', 'max' => 300],
            [['successurl', 'failurl', 'cancelurl'], 'url'],
            [['successurl', 'failurl', 'cancelurl'], 'string', 'max' => 1000],
            [['descript'], 'string', 'max' => 200],
        ];
    }

    /**
     * @param $paySchetId
     * @return string
     */
    public function getPayForm($paySchetId)
    {
        return Yii::$app->params['domain'] . '/p2p/form/' . $paySchetId;
    }

}
