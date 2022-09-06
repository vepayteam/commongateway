<?php

namespace app\services\callbacks\forms;

use app\services\payment\models\PaySchet;
use yii\base\Model;

class PaylerCallbackForm extends Model
{
    /**
     * @var string
     */
    public $order_id;

    /**
     * @var string|null
     */
    public $recurrent_template_id;

    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        return [
            [['order_id'], 'required'],
            [['order_id'], 'exist', 'targetClass' => PaySchet::class, 'targetAttribute' => 'ID'],
            [['recurrent_template_id'], 'string'],
        ];
    }

    /**
     * @return string
     */
    public function getOrderId(): string
    {
        return $this->order_id;
    }

    /**
     * @return string|null
     */
    public function getRecurrentTemplateId(): ?string
    {
        return $this->recurrent_template_id;
    }
}