<?php


namespace app\services\ident\responses;


use yii\base\Model;

class UploadCertRunaResponse extends Model
{
    public $certificate;
    public $state_code;
    public $state_description;

    /**
     * @return bool
     */
    public function isSuccess()
    {
        return $this->state_code == '00000';
    }
}
