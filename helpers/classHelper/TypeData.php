<?php

namespace app\helpers\classHelper;

use yii\base\BaseObject;

/**
 * Тип.
 */
class TypeData extends BaseObject
{
    /**
     * @var string|null
     */
    public $name;
    /**
     * @var bool
     */
    public $isArray = false;
    /**
     * @var bool
     */
    public $isClass = false;
}