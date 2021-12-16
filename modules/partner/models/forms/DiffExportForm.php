<?php

namespace app\modules\partner\models\forms;

use yii\base\Model;
use yii\helpers\Json;

class DiffExportForm extends Model
{
    /**
     * @var string
     */

    public $badStatus;

    /**
     * @var string
     */
    public $notFound;

    /**
     * @var string
     */
    public $format;

    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        return [
            [['badStatus', 'notFound', 'format'], 'required'],
            [['badStatus', 'notFound', 'format'], 'string'],
            [['format'], 'in', 'range' => ['csv', 'xlsx']],
        ];
    }

    public function getBadStatus(): array
    {
        return Json::decode($this->badStatus);
    }

    public function getNotFound(): array
    {
        return Json::decode($this->notFound);
    }
}