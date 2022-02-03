<?php

namespace components\client;

/**
 * Данные для запроса.
 */
interface RequestData
{
    /**
     * Возвращает ассоциативный массив с параметрами запроса.
     *
     * @return array
     */
    public function getData(): array;
}