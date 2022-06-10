<?php

namespace app\modules\h2hapi\v1\objects;

use app\components\api\ApiObject;

/**
 * HTTP Headers.
 */
class PaymentHeaderMapObject extends ApiObject
{
    /**
     * @var string
     */
    public $userAgent;
    /**
     * @var string
     */
    public $accept;

    /**
     * {@inheritDoc}
     */
    public function rules(): array
    {
        return [
            [['userAgent', 'accept'], 'required'],
            [['userAgent', 'accept'], 'string', 'max' => 255],
        ];
    }
}