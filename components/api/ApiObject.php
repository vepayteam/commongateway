<?php

namespace app\components\api;

use app\helpers\ClassHelper;
use Exception;
use ReflectionException;
use yii\base\Model;

/**
 * Объект, передаваемый/возвращаемый API.
 *
 * Добавляет: обработку вложенных форм, валидацию значений атрибутов в соответствии с phpDoc,
 * специальный вывод ошибок для API.
 */
class ApiObject extends Model
{
    /**
     * {@inheritDoc}
     * @throws ReflectionException
     * @throws Exception
     */
    public function setAttributes($values, $safeOnly = true)
    {
        if ($safeOnly) {
            // Обработка вложенных объектов
            $types = ClassHelper::getAttributeTypes($this);

            foreach ($this->activeAttributes() as $attr) {
                if (!isset($types[$attr]) || !array_key_exists($attr, $values)) {
                    continue;
                }
                $type = $types[$attr];

                /** @todo Реализовать обработку массивов */
                if (!$type->isArray) {
                    if ($type->isClass) {
                        if (!is_subclass_of($type->name, Model::class)) {
                            throw new Exception("Attribute {$attr} should be of type yii\base\Model.");
                        }
                        if (is_array($values[$attr])) {
                            /** @var Model $model */
                            if (isset($this->{$attr})) {
                                $model = $this->{$attr};
                            } else {
                                $model = new $type->name();
                                $this->{$attr} = $model;
                            }
                            $model->setAttributes($values[$attr], true);
                            unset($values[$attr]);
                        }
                    }
                }
            }

        }

        parent::setAttributes($values, $safeOnly);
    }

    /**
     * {@inheritDoc}
     * @throws ReflectionException
     */
    public function validate($attributeNames = null, $clearErrors = true): bool
    {
        if ($attributeNames === null) {
            $attributeNames = $this->activeAttributes();
        }
        if ($clearErrors) {
            $this->clearErrors();
        }

        $types = ClassHelper::getAttributeTypes($this);

        foreach ($attributeNames as $attr) {
            $value = $this->{$attr};
            if ($this->hasErrors($attr) || !isset($types[$attr]) || $value === null) {
                continue;
            }
            $attrType = $types[$attr];
            $valueTypeName = $this->getTypeName($value);

            /** @todo Реализовать обработку массивов */
            if (!$attrType->isArray) {
                if ($attrType->isClass) {
                    if ($value instanceof Model) {
                        if (!$value->validate()) {
                            $this->addError($attr, $value->getErrors());
                        }
                    } else {
                        $this->addTypeError($attr, 'object');
                    }
                    unset($attributeNames[$attr]);
                } elseif ($valueTypeName !== $attrType->name) {
                    $this->addTypeError($attr, $attrType->name);
                }
            }
        }

        parent::validate($attributeNames, false);

        return !$this->hasErrors();
    }

    private function getTypeName($value): string
    {
        /** @todo Заменить на get_debug_type() в PHP 8. */
        $type = gettype($value);
        $replace = ['boolean' => 'bool', 'integer' => 'int', 'double' => 'float'];
        if (isset($replace[$type])) {
            $type = $replace[$type];
        }
        return $type;
    }

    private function addTypeError(string $attr, string $expected)
    {
        $this->addError($attr, "Неверный тип «{$attr}». Ожидается: {$expected}.");
    }

    /**
     * {@inheritDoc}
     */
    public function createValidators(): \ArrayObject
    {
        $validators = parent::createValidators();
        foreach ($validators as $validator) {
            $validator->isEmpty = static function ($value) {
                return $value === null;
            };
        }
        return $validators;
    }

    /**
     * {@inheritDoc}
     * @throws ReflectionException
     */
    public function getFirstErrors(): array
    {
        $firstErrors = parent::getFirstErrors();

        $types = ClassHelper::getAttributeTypes($this);

        foreach ($this->activeAttributes() as $attr) {
            $value = $this->{$attr};
            if (!isset($types[$attr]) || empty($value)) {
                continue;
            }
            $type = $types[$attr];

            /** @todo Реализовать обработку массивов */
            if (!$type->isArray) {
                if ($type->isClass && $value instanceof Model && $value->hasErrors()) {
                    $firstErrors[$attr] = $value->getFirstErrors();
                }
            }
        }

        return $firstErrors;
    }

    /**
     * {@inheritDoc}
     */
    public function getAttributeLabel($attribute): string
    {
        return $attribute;
    }
}