<?php

namespace Vepay\Gateway\Client\Validator;

use Exception;

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
     * @throws Exception
     */
    public function validate(array $parameters): array
    {
        foreach ($this->rules as $parameter => $rule) {
            if ($rule === static::REQUIRED && empty($parameters[$parameter])) {
                throw new Exception("Required parameter '{$parameter}' is not defined.");
            }
        }

        return array_intersect_key($parameters, $this->rules);
    }
}