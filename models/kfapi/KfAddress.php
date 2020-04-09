<?php

namespace app\models\kfapi;

use yii\base\Model;

class KfAddress extends Model
{
    public $country;
    public $region;
    public $district;
    public $city;
    public $settlement;
    public $street;
    public $house;
    public $build;
    public $flat;

    public function rules()
    {
        return [
            [['country', 'region', 'district', 'city', 'settlement', 'street', 'house', 'build', 'flat'], 'string', 'max' => 100]
        ];
    }

    public function GetError()
    {
        $err = $this->firstErrors;
        $err = array_pop($err);
        return $err;
    }
}