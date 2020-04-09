<?php


namespace app\models\sms;


Interface ISms
{
    /**
     * Отправляет смс сообщения
    */
    public function send(): void;

    /**
     * Возвращает ответ от сервиса который предоставлет рассылку смс
    */
    public function response(): array;

    /**
     * Говорит о том удачно ли прошла отправка смс (анализирует ответ от сервера).
    */
    public function successful(): bool;
}