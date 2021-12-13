<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * @see ActiveRecord
 */
trait ModelHelper
{
    /**
     * @var bool
     */
    public $loaded = false;

    /**
     * @param array $data
     * @param string|null $formName
     * @param bool $validate
     * @param string|array|null $attributeNames
     * @param bool $clearErrors
     *
     * @return bool
     */
    public function load($data, $formName = null, bool $validate = false, $attributeNames = null, bool $clearErrors = true): bool
    {
        return $validate ? parent::load($data, $formName) && parent::validate($attributeNames, $clearErrors) : parent::load($data, $formName);
    }

    /**
     * @param string|array|null $attributeNames
     * @param bool $clearErrors
     * @param array|null $data
     * @param string|null $formName
     *
     * @return bool
     */
    public function validate($attributeNames = null, $clearErrors = true, ?array $data = null, ?string $formName = null): bool
    {
        return $data ? parent::load($data, $formName) && parent::validate($attributeNames, $clearErrors) : parent::validate($attributeNames, $clearErrors);
    }

    public function isLoaded(): bool
    {
        return $this->loaded;
    }

    public function setLoaded(bool $loaded): void
    {
        $this->loaded = $loaded;
    }
}
