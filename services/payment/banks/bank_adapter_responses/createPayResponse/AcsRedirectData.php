<?php

namespace app\services\payment\banks\bank_adapter_responses\createPayResponse;

use yii\base\Arrayable;
use yii\base\ArrayableTrait;

/**
 * @property-read int $status
 * @property-read string $url 3DS page URL to redirect the user to.
 * @property-read string $method "POST" (by JavaScript form submit) or "GET",
 * HTTP-method of redirection to 3DS page.
 * @property-read array $postParameters POST-parameters to send with form.
 */
class AcsRedirectData extends BaseAcsData implements Arrayable
{
    use ArrayableTrait;

    public const STATUS_OK = 'OK';
    public const STATUS_PENDING = 'PENDING';

    private $_status;
    private $_url;
    private $_method;
    private $_postParameters;

    public function __construct(
        string $status,
        $url = null,
        $method = null,
        $postParameters = null
    )
    {
        parent::__construct();

        $this->_status = $status;
        $this->_url = $url;
        $this->_method = $method;
        $this->_postParameters = $postParameters;
    }

    public function getStatus(): string
    {
        return $this->_status;
    }

    public function getUrl()
    {
        return $this->_url;
    }

    public function getMethod()
    {
        return $this->_method;
    }

    public function getPostParameters()
    {
        return $this->_postParameters;
    }

    /**
     * Used in AJAX response.
     * @todo Remove. Split the business logic and the controller/view layers.
     *
     * {@inheritDoc}
     */
    public function fields(): array
    {
        return [
            'type' => function () {
                return 'redirect';
            },
            'status',
            'url',
            'method',
            'postParameters',
        ];
    }
}