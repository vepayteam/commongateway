<?php

namespace app\models;

use yii\db\ActiveRecord;

use function method_exists;

/**
 * @see ActiveRecord
 */
trait ModelHelper
{
    /**
     * @var bool
     */
    public $loaded = false;

    public function load(array $data, string $formName = null): ActiveRecord
    {
        $this->setLoaded(method_exists('parent', 'load') && parent::load($data, $formName));
        return $this;
    }

    /**
     * @param bool $runValidation
     * @param array|string $attributeNames
     *
     * @return bool
     */
    public function save(bool $runValidation = true, $attributeNames = null): bool
    {
        return method_exists('parent', 'save') && $this->isLoaded() && parent::save($runValidation, $attributeNames);
    }

    /**
     * @param array|string $attributeNames
     * @param bool $clearErrors
     *
     * @return bool
     */
    public function validate($attributeNames = null, bool $clearErrors = true): bool
    {
        return method_exists('parent', 'validate') && $this->isLoaded() && parent::validate($attributeNames, $clearErrors);
    }

    public function isLoaded(): bool
    {
        return $this->loaded;
    }

    public function setLoaded(bool $loaded): ActiveRecord
    {
        $this->loaded = $loaded;
        return $this;
    }
}
