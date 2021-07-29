<?php

namespace app\services\base;

use app\services\base\exceptions\InvalidInputParamException;
use yii\base\Arrayable;

/**
 * @TODO: в продакшн ещё не вмёрджен коммит с другой реализацией Structure,
 * удалить после мерджа ту, предварительно заменив все использования.
 *
 * Class Structure
 *
 * @package app\services\base
 */
class Structure implements Arrayable
{
    /**
     * Structure constructor.
     *
     * @param array|null $data
     */
    public function __construct(array $data = [])
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

    /**
     * @inheritDoc
     */
    public function fields(): array
    {
        return $this->toArray();
    }

    /**
     * @inheritDoc
     */
    public function extraFields(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function toArray(array $fields = [], array $expand = [], $recursive = true): array
    {
        $result = [];

        if (empty($fields)) {
            $fields = array_keys(get_class_vars(static::class));
        }

        foreach ($fields as $field) {
            if (isset($this->{$field})) {
                $result[$field] = (($this->{$field} instanceof Structure) ? $this->{$field}->toArray() : $this->{$field});
            } else {
                $result[$field] = null;
            }
        }

        return $result;
    }
}
