<?php


namespace app\services\ident\responses;


use app\services\ident\models\IdentRuna;
use yii\base\Model;

class RunaIdentInitResponse extends Model
{
    /** @var IdentRuna */
    public $identRuna;

    public $id;
    public $tid;
    public $state_code;
    public $state_description;

}
