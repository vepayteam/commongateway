<?php

namespace app\modules\partner\models\forms;

use app\services\payment\models\Bank;
use yii\base\Model;
use yii\web\UploadedFile;

class DiffDataForm extends Model
{
    /**
     * @var int
     */
    public $bank;

    /**
     * @var UploadedFile
     */
    public $registryFile;

    /**
     * @var int
     */
    public $registrySelectColumn;

    /**
     * @var int
     */
    public $registryStatusColumn;

    /**
     * @var int
     */
    public $allRegistryStatusSuccess;

    /**
     * @var string
     */
    public $dbColumn;

    /**
     * @var string[]
     */
    public $statuses;

    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        return [
            [['bank', 'registryFile', 'registrySelectColumn', 'registryStatusColumn', 'dbColumn'], 'required'],
            [['bank', 'registrySelectColumn', 'registryStatusColumn'], 'number'],
            [['bank'], 'validateBank'],
            [['allRegistryStatusSuccess'], 'boolean'],
            [['allRegistryStatusSuccess'], 'default', 'value' => false],
            [['registryFile'], 'file'],
            [['dbColumn'], 'string'],
            ['statuses', 'each', 'rule' => ['string']],
        ];
    }

    /**
     * @return bool
     */
    public function validateBank(): bool
    {
        return !empty(Bank::findOne(['ID' => $this->bank]));
    }

    /**
     * @param int $status
     * @return string|null
     */
    public function getStatusFor(int $status): ?string
    {
        if (!array_key_exists($status, $this->statuses)) {
            return null;
        }

        return $this->statuses[$status];
    }
}
