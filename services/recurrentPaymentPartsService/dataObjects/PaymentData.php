<?php

namespace app\services\recurrentPaymentPartsService\dataObjects;

/**
 * Данные для оплаты.
 */
interface PaymentData
{
    /**
     * Полная сумма в дробных частях валюты (fractional unit): копейках, центах.
     */
    public function getTotalAmountFractional(): int;

    /**
     * ID Карты.
     */
    public function getCardId(): int;

    /**
     * Внешний идентификатор запроса.
     */
    public function getExternalId(): string;

    /**
     * Номер договора.
     */
    public function getDoumentId(): string;

    /**
     * Описание.
     */
    public function getDescription(): string;

    /**
     * ФИО клиента.
     */
    public function getFullname(): string;

    /**
     * @return PartData[]
     */
    public function getParts(): array;
}