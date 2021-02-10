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

    protected array $rules = [];

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
        foreach ($this->rules as $parameter => $rule) {
            if (empty($parameters[$parameter])) {
                if ($rule === static::REQUIRED) {
                    throw new ValidationException("Required parameter '{$parameter}' is not defined.",422);
                } else {
                    Config::getInstance()->logger->warning("Optional field '{$parameter}' is empty.", __CLASS__);
                }
            }
        }

        return array_intersect_key($parameters, $this->rules);
    }
}