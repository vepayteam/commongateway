<?php

namespace app\helpers;

use app\helpers\classHelper\ExtendedReflectionClass;
use app\helpers\classHelper\TypeData;
use phpDocumentor\Reflection\DocBlock\Tags\Property;
use phpDocumentor\Reflection\DocBlock\Tags\PropertyRead;
use phpDocumentor\Reflection\DocBlock\Tags\PropertyWrite;
use phpDocumentor\Reflection\DocBlock\Tags\Var_;
use phpDocumentor\Reflection\DocBlockFactory;
use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\Types\Array_;
use phpDocumentor\Reflection\Types\Compound;
use phpDocumentor\Reflection\Types\Mixed_;
use phpDocumentor\Reflection\Types\Object_;

/**
 * Содержит функции для получения информации о классах PHP.
 */
class ClassHelper
{
    private static $classAttributeTypesCached = [];

    /**
     * Возвращает типы атрибутов класса.
     *
     * @param object|string $class Объект или имя класса.
     * @return TypeData[] Массив с именами атрибутов класса в качестве ключей и их типами в качестве значений.
     * @throws \ReflectionException
     */
    public static function getAttributeTypes($class): array
    {
        // Вернем ранее сохраненный результат, если есть
        $className = is_object($class) ? get_class($class) : $class;
        if (isset(self::$classAttributeTypesCached[$className])) {
            return self::$classAttributeTypesCached[$className];
        }

        /** @var TypeData[] $allFields */
        $allFields = [];

        $docBlockFactory = DocBlockFactory::createInstance();
        $reflectionClass = new ExtendedReflectionClass($class);

        // Получаем цепочку наследуемых классов
        /** @var ExtendedReflectionClass[] $chain */
        $chain = [];
        $parent = $reflectionClass;
        do {
            $parent = $parent->getParentClass();
            if ($parent) {
                $parent = new ExtendedReflectionClass($parent->getName());
                array_unshift($chain, $parent);
            }
        } while ($parent);
        $chain[] = $reflectionClass;

        foreach ($chain as $chainReflectionClass) {
            // Разбор шапки
            $docComment = $chainReflectionClass->getDocComment();
            if ($docComment !== false) {
                $docBlock = $docBlockFactory->create($docComment);
                foreach ($docBlock->getTags() as $tag) {
                    if (($tag instanceof Property) | ($tag instanceof PropertyRead) | ($tag instanceof PropertyWrite)) {
                        $allFields[$tag->getVariableName()] = self::createTypeObject($tag->getType(), $chainReflectionClass);
                    }
                }
            }

            // Проход по публичным свойствам класса
            $props = $chainReflectionClass->getProperties(\ReflectionProperty::IS_PUBLIC);
            foreach ($props as $prop) {
                $comment = $prop->getDocComment();
                $docblock = $docBlockFactory->create($comment ?: '   ');
                $varTags = $docblock->getTagsByName('var');

                $type = null;
                if (isset($varTags[0])) {
                    /* @var Var_ $tag */
                    $tag = $varTags[0];
                    // https://github.com/symfony/symfony/issues/36049
                    if (!method_exists($tag, 'getType')) {
                        continue;
                    }
                    $type = self::createTypeObject($tag->getType(), $chainReflectionClass);
                }
                $allFields[$prop->getName()] = $type;
            }
        }

        self::$classAttributeTypesCached[$className] = $allFields;
        return $allFields;
    }

    /**
     * @param Type $type
     * @param ExtendedReflectionClass $reflectionClass
     * @return TypeData
     */
    private static function createTypeObject(Type $type, ExtendedReflectionClass $reflectionClass): TypeData
    {
        $isArray = false;

        /**
         * Если тип составной, то выбираем из его подтипов массив объектов.
         * Например, из типа "array|Models[]|int[]" будет взят "Models[]".
         * Если такой подтип не найден, будет взят первый элемент (подтип) составного типа.
         */
        if ($type instanceof Compound) {
            $preferredType = null;
            foreach ($type as $subtype) {
                /** @var Type $subtype */
                if ($subtype instanceof Array_) {
                    if ($subtype->getValueType() instanceof Object_) {
                        $preferredType = $subtype;
                    }
                }
            }
            if ($preferredType === null) {
                $preferredType = $type->get(0);
            }
            $type = $preferredType;
        }

        if ($type instanceof Array_) {
            $valueType = $type->getValueType();
            if ($type->getValueType() instanceof Mixed_) {
                return new TypeData(['name' => 'array']);
            }
            $type = $valueType;
            $isArray = true;
        }

        $typeData = new TypeData();
        $typeData->isArray = $isArray;
        if ($type instanceof Object_) {
            $typeData->name = self::fixClass((string)$type, $reflectionClass);
            $typeData->isClass = true;
        } else {
            $typeData->name = (string)$type;
        }

        return $typeData;
    }

    /**
     * @param string $class
     * @param ExtendedReflectionClass $reflectionClass
     * @return mixed|string
     */
    private static function fixClass(string $class, ExtendedReflectionClass $reflectionClass)
    {
        if (strpos($class, '\\') === 0) {
            $class = substr($class, 1);
        }
        if (isset($reflectionClass->getUseStatements()[$class])) {
            return $reflectionClass->getUseStatements()[$class];
        } else {
            return $reflectionClass->getNamespaceName() . '\\' . $class;
        }
    }
}