<?php

use yii\db\Migration;
use yii\db\Query;

/**
 * Class m210810_151333_add_h2h_uslugatovar_types
 */
class m210810_151333_add_h2h_uslugatovar_types extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        /**
         * Добавлена проверка существования на случай, если миграции запущены с нуля,
         * так как в одной из предыдущих миграций {@see m201012_062012_create_uslugatovar_types} происходит
         * добавление записей из МЕТОДА КЛАССА {@see \app\services\payment\models\UslugatovarType::getAll()}!
         */
        foreach ($this->types() as $id => $name) {
            if (!(new Query())->from('uslugatovar_types')->andWhere(['Id' => $id])->exists()) {
                $this->insert('uslugatovar_types', [
                    'Id' => $id,
                    'Name' => $name,
                ]);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        foreach (array_keys($this->types()) as $id) {
            $this->delete('uslugatovar_types', ['Id' => $id]);
        }
    }

    private function types(): array
    {
        return [
            200 => 'H2H Погашение займа AFT',
            201 => 'H2H погашение займа ECOM',
            202 => 'H2H оплата товаров и услуг',
        ];
    }
}
