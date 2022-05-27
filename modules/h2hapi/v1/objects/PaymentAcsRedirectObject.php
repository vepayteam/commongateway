<?php

namespace app\modules\h2hapi\v1\objects;

use app\models\PaySchetAcsRedirect;
use yii\base\Model;

/**
 * ACL (3DS) redirect data. Read-only.
 */
class PaymentAcsRedirectObject extends Model
{
    public const STATUS_OK = 'OK';
    public const STATUS_PENDING = 'PENDING';

    /**
     * @var string
     */
    public $status;
    /**
     * @var string|null
     */
    public $url;
    /**
     * @var string|null
     */
    public $method;
    /**
     * @var array|null
     */
    public $postParameters;

    /**
     * {@inheritDoc}
     */
    public function fields(): array
    {
        return [
            'status',
            'url',
            'method',
            'postParameters',
        ];
    }

    /**
     * @param PaySchetAcsRedirect $paySchetAcsRedirect
     * @return $this
     */
    public function mapPaySchetAcsRedirect(PaySchetAcsRedirect $paySchetAcsRedirect): PaymentAcsRedirectObject
    {
        $this->status = [
            PaySchetAcsRedirect::STATUS_OK => static::STATUS_OK,
            PaySchetAcsRedirect::STATUS_PENDING => static::STATUS_PENDING,
        ][$paySchetAcsRedirect->status];

        $this->url = $paySchetAcsRedirect->url;
        $this->method = $paySchetAcsRedirect->method;
        $this->postParameters = $paySchetAcsRedirect->postParameters;

        return $this;
    }
}