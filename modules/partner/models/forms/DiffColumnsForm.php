<?php

namespace app\modules\partner\models\forms;

use app\services\payment\models\Bank;
use yii\base\Model;

class DiffColumnsForm extends Model
{
    /**
     * @var int
     */
    public $bank;

    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        return [
            [['bank'], 'required'],
            [['bank'], 'number'],
            [['bank'], 'validateBank'],
        ];
    }

    /**
     * @return bool
     */
    public function validateBank(): bool
    {
        return !empty(Bank::findOne(['ID' => $this->bank]));
    }
}
