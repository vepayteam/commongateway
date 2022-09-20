<?php

namespace app\components;

use yii\base\InvalidCallException;
use yii\base\UnknownPropertyException;

/**
 * Immutable data transfer object where constructor parameters are read-only properties.
 *
 * Usage:
 * ```
 * class A extends ImmutableDataObject {
 *  public function __construct(int $foo, int $bar) {
 *      parent::__construct(get_defined_vars());
 *  }
 * }
 * ```
 */
abstract class ImmutableDataObject
{
    private $_properties;

    public function __construct(array $properties = [])
    {
        $this->_properties = $properties;
    }

    /**
     * @throws UnknownPropertyException
     */
    public function __get(string $name)
    {
        if (array_key_exists($name, $this->_properties)) {
            return $this->_properties[$name];
        }
        throw new UnknownPropertyException('Getting unknown property: ' . get_class($this) . '::' . $name);
    }

    public function __set(string $name, $value)
    {
        throw new InvalidCallException('Setting read-only property: ' . get_class($this) . '::' . $name);
    }

    public function __isset($name)
    {
        if (array_key_exists($name, $this->_properties)) {
            return $this->_properties[$name] !== null;
        }
        return false;
    }

    /**
     * @throws UnknownPropertyException
     */
    public function __unset(string $name)
    {
        if (!array_key_exists($name, $this->_properties)) {
            throw new UnknownPropertyException('Unsetting unknown property: ' . get_class($this) . '::' . $name);
        }
        throw new InvalidCallException('Unsetting read-only property: ' . get_class($this) . '::' . $name);
    }
}