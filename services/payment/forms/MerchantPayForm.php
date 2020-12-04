<?php


namespace app\services\payment\forms;


class MerchantPayForm extends BaseForm
{
    public $type;
    public $amount = 0;
    public $document_id = '';
    public $fullname = '';
    public $extid = '';
    public $descript = '';
    public $id;
    //public $type = 0;/*'type', */
    public $card = 0;
    public $timeout = 15;
    public $successurl = '';
    public $failurl = '';
    public $cancelurl = '';
    public $postbackurl = '';
    public $postbackurl_v2 = '';

    public function rules()
    {
        return [
            [['successurl', 'document_id', 'fullname', 'timeout'], 'required'],
            [['type'], 'integer', 'min' => 0],
            [['amount'], 'number', 'min' => 1, 'max' => 1000000],
            [['extid'], 'string', 'max' => 40],
            [['document_id'], 'string', 'max' => 40],
            [['fullname'], 'string', 'max' => 80],
            [['successurl', 'failurl', 'cancelurl', 'postbackurl', 'postbackurl_v2'], 'url'],
            [['successurl', 'failurl', 'cancelurl', 'postbackurl', 'postbackurl_v2'], 'string', 'max' => 300],
            [['descript'], 'string', 'max' => 200],
            [['timeout'], 'integer', 'min' => 10, 'max' => 59],
            [['amount'], 'required'],
            [['amount', 'card'], 'required'],
        ];
    }

}
