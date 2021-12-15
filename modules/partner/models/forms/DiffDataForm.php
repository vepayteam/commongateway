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
            [['bank', 'registryFile', 'registrySelectColumn', 'dbColumn'], 'required'],
            [['registryStatusColumn'], 'required', 'when' => function ($model) {
                return !$model->allRegistryStatusSuccess;
            }],
            [['bank', 'registrySelectColumn', 'registryStatusColumn'], 'number'],
            [['bank'], 'exist', 'targetClass' => Bank::class, 'targetAttribute' => 'ID'],
            [['allRegistryStatusSuccess'], 'boolean'],
            [['allRegistryStatusSuccess'], 'default', 'value' => false],
            [['registryFile'], 'file'],
            [['dbColumn'], 'string'],
            ['statuses', 'each', 'rule' => ['string']],
        ];
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
