<?php


namespace app\models\sms;


interface IMessage
{
    /**
     * Проверяет валиден ли класс (если запустить из базы возьмется телефон и проверится
     * также в этом методе должно валидироваться сообщение
    */
    public function validated():bool;

    /**
    * Возрващает массив телефонов для отправки
    */
    public function phones(): array;

    /**
     * Возвращает сообщение которое нужно отправить.
    */
    public function content(): string;
}