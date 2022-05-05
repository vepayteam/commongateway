<?php

namespace app\modules\h2hapi\v1\objects;

use app\components\api\ApiObject;

/**
 * Client's browser data.
 */
class PaymentBrowserDataObject extends ApiObject
{
    /**
     * @var int Browser area height.
     */
    public $screenHeight;
    /**
     * @var int Browser area width.
     */
    public $screenWidth = null;
    /**
     * @var int
     */
    public $windowHeight;
    /**
     * @var int
     */
    public $windowWidth;
    /**
     * @var int Difference between browser timezone and UTC time in minutes. Example: -180.
     */
    public $timezoneOffset;
    /**
     * @var int
     */
    public $language;
    /**
     * @var int  Device color depth. Example: 24.
     */
    public $colorDepth;
    /**
     * @var bool
     */
    public $javaEnabled;

    /**
     * {@inheritDoc}
     */
    public function rules(): array
    {
        return [
            [
                [
                    'screenHeight', 'screenWidth', 'timezoneOffset', 'windowHeight', 'windowWidth',
                    'language', 'colorDepth', 'javaEnabled'
                ],
                'required',
            ],
            [
                ['screenHeight', 'screenWidth', 'timezoneOffset', 'windowHeight', 'windowWidth', 'colorDepth'],
                'integer',
            ],
            [['javaEnabled'], 'boolean'],
            [['language'], 'string', 'max' => 5],
        ];
    }
}