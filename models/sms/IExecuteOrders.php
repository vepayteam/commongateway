<?php


namespace app\models\sms;


interface IExecuteOrders
{
    /**
     * Выполняет отправку ордера на оплату
    */
    public function execute(): void;

    /**
     * Говори о том удачно ли прошла отправка ордера на оплату.
    */
    public function successful(): bool;

    /**
     * Возвращает сообщение при удачной работе класса.
    */
    public function successfulMessage(): string;

    /**
     * Возвращает ошибки которые возникли в ходе выполнения задач
    */
    public function errors(): array;
}