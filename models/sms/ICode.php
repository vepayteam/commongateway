<?php


namespace app\models\sms;


use app\models\sms\tables\Sms;

interface ICode
{
    /**
     * Возвращает код который был передан в класс
     * @return string
     */
    public function code(): string;

    /**
     * Указывает - прошел ли код проверку, если не прошел то ошибки можно получить через методо
     * $this->errors
     * @return bool
     */
    public function confirmed(): bool;

    /**
     * Возвращает ошибки при проверке кода.
     * @return array[][int=>string]
     */
    public function errors(): array;

    /**
     * Возвращает модель SMS к которой принадлежит данный код.
     * @return Sms
    */
    public function sms();
}