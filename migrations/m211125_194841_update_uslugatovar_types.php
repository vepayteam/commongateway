<?php

use yii\db\Migration;

/**
 * Class m211125_194841_update_uslugatovar_types
 */
class m211125_194841_update_uslugatovar_types extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->update('uslugatovar_types', ['Name' => 'H2H платёж AFT'], ['Id' => 200]);
        $this->update('uslugatovar_types', ['Name' => 'H2H платёж ECOM'], ['Id' => 201]);
        $this->update('uslugatovar_types', ['Name' => 'P2P перевод с карты на карту'], ['Id' => 26]);
        $this->update('uslugatovar_types', ['Name' => 'Автоплатёж AFT'], ['Id' => 12]);
        $this->update('uslugatovar_types', ['Name' => 'Автоплатёж AFT с разбивкой'], ['Id' => 112]);
        $this->update('uslugatovar_types', ['Name' => 'Автоплатёж ECOM'], ['Id' => 16]);
        $this->update('uslugatovar_types', ['Name' => 'Автоплатёж ECOM с разбивкой'], ['Id' => 116]);
        $this->update('uslugatovar_types', ['Name' => 'Вывод комиссии VEPAY'], ['Id' => 17]);
        $this->update('uslugatovar_types', ['Name' => 'Вывод средств на р/сч'], ['Id' => 19]);
        $this->update('uslugatovar_types', ['Name' => 'Выплата на карту'], ['Id' => 13]);
        $this->update('uslugatovar_types', ['Name' => 'Выплата через СБП'], ['Id' => 203]);
        $this->update('uslugatovar_types', ['Name' => 'Платёж AFT'], ['Id' => 10]);
        $this->update('uslugatovar_types', ['Name' => 'Платёж AFT с разбивкой'], ['Id' => 110]);
        $this->update('uslugatovar_types', ['Name' => 'Платёж ECOM'], ['Id' => 14]);
        $this->update('uslugatovar_types', ['Name' => 'Платёж ECOM с разбивкой'], ['Id' => 114]);
        $this->update('uslugatovar_types', ['Name' => 'Привязка карты'], ['Id' => 1]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->update('uslugatovar_types', ['Name' => 'H2H Погашение займа AFT'], ['Id' => 200]);
        $this->update('uslugatovar_types', ['Name' => 'H2H погашение займа ECOM'], ['Id' => 201]);
        $this->update('uslugatovar_types', ['Name' => 'Перевод с карты на карту'], ['Id' => 26]);
        $this->update('uslugatovar_types', ['Name' => 'Автоплатеж по займу AFT'], ['Id' => 12]);
        $this->update('uslugatovar_types', ['Name' => 'Автоплатеж по займу AFT с разбивкой'], ['Id' => 112]);
        $this->update('uslugatovar_types', ['Name' => 'Автоплатеж по займу ECOM'], ['Id' => 16]);
        $this->update('uslugatovar_types', ['Name' => 'Автоплатеж по займу ECOM с разбивкой'], ['Id' => 116]);
        $this->update('uslugatovar_types', ['Name' => 'Комиссия'], ['Id' => 17]);
        $this->update('uslugatovar_types', ['Name' => 'Вывод средств'], ['Id' => 19]);
        $this->update('uslugatovar_types', ['Name' => 'Выдача займа на карту'], ['Id' => 13]);
        $this->update('uslugatovar_types', ['Name' => 'Перевод B2C SBP'], ['Id' => 203]);
        $this->update('uslugatovar_types', ['Name' => 'Погашение займа AFT'], ['Id' => 10]);
        $this->update('uslugatovar_types', ['Name' => 'Погашение займа AFT с разбивкой'], ['Id' => 110]);
        $this->update('uslugatovar_types', ['Name' => 'Погашение займа ECOM'], ['Id' => 14]);
        $this->update('uslugatovar_types', ['Name' => 'Погашение займа ECOM с разбивкой'], ['Id' => 114]);
        $this->update('uslugatovar_types', ['Name' => 'Регистрация карты'], ['Id' => 1]);
    }
}

/**  */
