<?php


namespace app\models\bank;


interface IBankGate
{
    /**
     * @param int $IdPartner Мерчант
     * @param int $typeGate Тип Шлюз
     * @param int|null $IsCustom Или тип услуги (для выбора шлюза по типу услуги)
     */
    public function __construct($IdPartner, $typeGate, $IsCustom = null);

    public static function GetIsCustomBankGates();

        /**
     * Шлюз по услуге
     * @param $IsCustom
     * @return int
     */
    public function SetTypeGate($IsCustom);

    /**
     * Шлюзы мерчанта
     * @return array|false|null
     * @throws \yii\db\Exception
     */
    public function GetGates();

    /**
     * Проверка настройки шлюза для мерчанта
     * @param $gate
     * @return bool
     * @throws \yii\db\Exception
     */
    public function IsGate();

    /**
     * @return int
     */
    public function getTypeGate();

    /**
     * @return int
     */
    public function getBank();
}
