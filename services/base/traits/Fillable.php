<?php

namespace app\services\base\traits;

use app\services\base\exceptions\InvalidInputParamException;

trait Fillable
{
    /**
     * @param array $data
     * @param bool  $throwException
     */
    public function fill(array $data = [], bool $throwException = false): void
    {
        if (!empty($data)) {
            foreach ($data as $key => $field) {
                if ($throwException && (!is_string($key) || !property_exists($this, $key))) {
                    throw new InvalidInputParamException('Unknown property: '.$key);
                } elseif (property_exists($this, $key)) {
                    $this->{$key} = $field;
                }
            }
        }
    }
}
