<?php

namespace app\models;

/**
 *
 */
trait ModelHelper
{
    /**
     * @var
     */
    public $isTrue;

    /**
     * @param $data
     * @param null $formName
     * @param false $validate
     * @param null $attributeNamesToValidate
     * @param bool $clearValidateErrors
     *
     * @return bool
     */
    public function loadAndValidate($data, $formName = null, bool $validate = false, $attributeNamesToValidate = null, bool $clearValidateErrors = true)
    {
        $loaded = parent::load($data, $formName);
        return $validate ? $loaded && $loaded->validate($attributeNamesToValidate, $clearValidateErrors) : $loaded;
    }

    /**
     * @param $data
     * @param null $formName
     *
     * @return $this
     */
    public function load($data, $formName = null)
    {
        $this->isTrue = $this->isTrue ? parent::load($data, $formName) : false;

        return $this;
    }

    /**
     * @param bool $runValidation
     * @param null $attributeNames
     *
     * @return $this
     */
    public function save(bool $runValidation = true, $attributeNames = null)
    {
        $this->isTrue = $this->isTrue ? parent::save($runValidation, $attributeNames) : false;

        return $this;
    }
}