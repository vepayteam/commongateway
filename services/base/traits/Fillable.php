<?php

namespace app\services\base\traits;

use app\services\base\exceptions\InvalidInputParamException;

trait Fillable
{
    /**
     * @param array $data
     */
    public function fill(array $data = []): void
    {
        if (!empty($data)) {
            foreach ($data as $key => $field) {
                if (!is_string($key) || !property_exists($this, $key)) {
                    throw new InvalidInputParamException('Unknown property: '.$key);
                }
                $this->{$key} = $field;
            }
        }
    }
}
