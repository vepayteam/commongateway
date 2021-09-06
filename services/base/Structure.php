<?php

namespace app\services\base;

use app\services\base\exceptions\InvalidInputParamException;
use yii\base\Arrayable;
use yii\base\ArrayableTrait;

/**
 * @TODO: в продакшн ещё не вмёрджен коммит с другой реализацией Structure,
 * удалить после мерджа ту, предварительно заменив все использования.
 *
 * Class Structure
 *
 * DTO, инстанциирующийся из ассоциативного массива
 */
class Structure implements Arrayable
{
    use ArrayableTrait;

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
}
