<?php

namespace app\models;

use app\models\partner\UserLk;
use Yii;
use yii\db\ActiveRecord;

/**
 * @see ActiveRecord
 * @property bool $isAdmin
 */
trait ModelHelper
{
    /**
     * @proretry bool $loaded
     */
    public $loaded = false;
    /**
     * @proretry bool $_isAdmin
     */
    private $_isAdmin = null;

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

    /**
     * @param array $data
     * @param string|null $formName
     * @param null|string|array $attributeNames
     * @param bool $clearErrors
     *
     * @return bool
     */
    public function loadAndValidate(array $data, ?string $formName = null, $attributeNames = null, bool $clearErrors = true): bool
    {
        return $this->load($data, $formName, true, $attributeNames, $clearErrors);
    }

    public function isLoaded(): bool
    {
        return $this->loaded;
    }

    public function setLoaded(bool $loaded): void
    {
        $this->loaded = $loaded;
    }

    public function getIsAdmin(): ?bool
    {
        $this->_isAdmin = $this->_isAdmin ?? (bool)UserLk::IsAdmin(Yii::$app->user);
        return $this->_isAdmin;
    }
}
