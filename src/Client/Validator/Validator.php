<?php

namespace Vepay\Gateway\Client\Validator;

use Vepay\Gateway\Config;

/**
 * Class Validator
 * @package Vepay\Gateway\Client\Validator
 */
class Validator
{
    public const REQUIRED = 'required';
    public const OPTIONAL = 'optional';

    /** @var array  */
    protected $rules = [];

    /**
     * @param string $name
     * @param string $rule
     * @return $this
     */
    public function set(string $name, string $rule): Validator
    {
        $this->rules[$name] = $rule;

        return $this;
    }

    /**
     * @param array $parameters
     * @return array
     * @throws ValidationException
     */
    public function validate(array $parameters): array
    {
        $validParameters = [];

        foreach ($this->rules as $fieldName => $rule) {
            if (strpos($fieldName, '*') > 0) {
                $validParameters += array_filter(
                    $parameters,
                    function ($parameter) use ($fieldName) {
                        return strpos($parameter, rtrim($fieldName, '*')) === 0;
                    },
                    ARRAY_FILTER_USE_KEY
                );

                continue;
            }

            if (!isset($parameters[$fieldName])) {
                if ($rule === static::REQUIRED) {
                    throw new ValidationException("Required parameter '{$fieldName}' is not defined.",422);
                } else {
                    Config::getInstance()->logger->warning("Optional field '{$fieldName}' is empty.", __CLASS__);
                }
            }
            $validParameters[$fieldName] = $parameters[$fieldName];
        }

        return array_intersect_key($parameters, $validParameters);
    }
}