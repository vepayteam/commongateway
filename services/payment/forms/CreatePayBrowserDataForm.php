<?php

namespace app\services\payment\forms;

use app\models\traits\ValidateFormTrait;
use yii\base\Model;

/**
 * Subform of {@see CreatePayForm}.
 */
class CreatePayBrowserDataForm extends Model
{
    /** @var int|null */
    public $screenHeight;
    /** @var int|null */
    public $screenWidth;
    /** @var int|null */
    public $timezoneOffset;
    /** @var bool|null */
    public $javaEnabled;
    /** @var int|null */
    public $windowHeight;
    /** @var int|null */
    public $windowWidth;
    /** @var int|null */
    public $colorDepth;
    /** @var string|null */
    public $language;

    /**
     * {@inheritDoc}
     */
    public function rules(): array
    {
        return [
            [
                // convert string numbers to true numbers
                ['screenHeight', 'screenWidth', 'timezoneOffset', 'windowHeight', 'windowWidth', 'colorDepth'],
                'filter',
                'filter' => function ($value) {
                    return is_numeric($value) ? ($value + 0) : $value;
                },
            ],
            [
                ['screenHeight', 'screenWidth', 'timezoneOffset', 'windowHeight', 'windowWidth', 'colorDepth'],
                'integer',
            ],
            [['language'], 'string'],
            [['javaEnabled'], 'boolean', 'trueValue' => true, 'falseValue' => false, 'strict' => true],
        ];
    }
}
