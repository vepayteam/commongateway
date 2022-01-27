<?php


namespace app\services\payment\forms\tkb;


use yii\base\Model;

class TransferToAccountRequest extends Model
{
    public $OrderId;
    public $Name;
    public $Inn = '';
    // public $Kpp = '';
    public $Bic;
    public $Account;
    public $Amount;
    public $Description;
}
