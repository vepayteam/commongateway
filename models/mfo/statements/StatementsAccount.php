<?php

namespace app\models\mfo\statements;

use yii\db\ActiveRecord;

/**
 * Class StatementsAccount
 *
 * @package app\models\mfo\statements
 * @property int    $ID
 * @property int    $IdPartner
 * @property int    $TypeAccount
 * @property int    $BnkId
 * @property int    $NumberPP
 * @property int    $DatePP
 * @property int    $SummPP
 * @property int    $SummComis
 * @property string $Description
 * @property int    $IsCredit
 * @property string $Name
 * @property string $Inn
 * @property string $Account
 * @property string $Bic
 * @property string $Bank
 * @property string $BankAccount
 * @property string $Kpp
 * @property int    $DateRead
 * @property int    $DateDoc
 */
class StatementsAccount extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'statements_account';
    }
}
