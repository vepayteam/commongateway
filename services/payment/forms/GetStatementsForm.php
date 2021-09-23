<?php


namespace app\services\payment\forms;


use yii\base\Model;

class GetStatementsForm extends Model
{
    /** @var int */
    public $dateFrom;
    /** @var int */
    public $dateTo;
}
