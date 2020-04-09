<?php

use app\models\antifraud\tables\AFRuleInfo;
use yii\db\Migration;

/**
 * Class m200206_032458_add_table_rule_info
 */
class m200206_032458_add_table_rule_info extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('antifraud_rule_info', [
            'id'=>$this->primaryKey(),
            'rule'=>$this->string(),
            'description'=>$this->string(),
            'rule_title'=>$this->string(),
            'critical_value'=>$this->string()
        ]);

        $rules = [
            'AsnRule',
            'BlackListCards',
            'CardMoreIps',
            'CardMoreOrders',
            'CountryCard',
            'IpMoreCards',
            'NightPay',
            'RussianTransaction',
        ];

        foreach ($rules as $rule){
            switch ($rule){
                case "AsnRule":
                    $rule_class = $rule;
                    $val = "На каждую 1000 транзакцию из подсети 1 не прошла проверку системой антифрода";
                    $desc = "Определяет ASN пользователя и сверяет его с БД, Если ASN находится в черном списке, или вообще отстутствует, то правило считается не выполненным.";
                    $title = "ASN фильтр.";
                    break;
                case "BlackListCards":
                    $rule_class = $rule;
                    $val = "Если ранее эта карта была отмечена как 'черная'.";
                    $desc = 'Если карта входит в состав блэклистов то правило считается не выполненным.';
                    $title = 'Черный список банковских карт.';
                    break;
                case "CardMoreIps":
                    $rule_class = $rule;
                    $val = "Если последние 100 операций, были сделаны из более чем 10 подсетей (ASN).";
                    $desc = "Соотносятся последние 100 операций (удачных и не удачных) по карте, и IP с которых они были до этого сделаны. Если IP принадлежат разным подсетям, и текущий IP не пренадлежит этим подсетям, то правило считаетс не выполненным";
                    $title = "Одна карта - много ip";
                    break;
                case "CardMoreOrders":
                    $rule_class = $rule;
                    $val = "Если за последние 5 операций хотябы одна была не выполненна.";
                    $desc = "Если несколько транзакций были отклонены, но используется одна и таже карта, то правило считается не выполненным.";
                    $title = 'Одна карта - много неудачных попыток';
                    break;
                case "CountryCard":
                    $rule_class = $rule;
                    $val = "Страна банка выпустившего карту не совпадает с текущей страной клиента.";
                    $desc = "Анализ карты введенной пользователем, и анализ его гео по IP, если страны не совпадают, то правило считается не выполненным.";
                    $title = "Страна выпуска карты не совпадает с текущим гео пользователя";
                    break;
                case "IpMoreCards":
                    $rule_class = $rule;
                    $val = "Если за последние 5 операций хотябы одна была не выполненна (с разных карт).";
                    $desc = "Если с одного IP выполняется множество транзакций по разным картам то правило считается не выполненным";
                    $title = "Один IP - много карт.";
                    break;
                case "NightPay":
                    $rule_class = $rule;
                    $title = "Оплата происходит в ночь.";
                    $val = "За основу берется время в часовом поясе пользователя.";
                    $desc = "Если в текущий момент у ГЕО пользователя ночь (с 23.00 до 7.00), то правило считается не выполненным";
                    break;
                case "RussianTransaction":
                    $title = "Последняя транзакция в РФ, последующая за ее пределами.";
                    $val = "Последняя страна оплаты не совпадает с текущей страной пользователя.";
                    $desc= "Если последняя транзакция была в РФ, а текущая в другой стране, то правило считается не выполненным.";
                    $rule_class = $rule;
                    break;
                case "":
                    $title = "Пользователь ранее опротестовывал транзакции и возвращал деньги.";
                    $val = "Если за последние 100 транзакций есть 5 отмененных.";
                    $desc = "Если у пользователя уже были случаи отмены транзакции, то правило считается не выполненным";
                    $rule_class = $rule;
                    break;
            }
            $record = new AFRuleInfo();
            $record->rule = $rule_class;
            $record->description = $desc;
            $record->rule_title = $title;
            $record->critical_value = $val;
            $record->save();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('antifraud_rule_info');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200206_032458_add_table_rule_info cannot be reverted.\n";

        return false;
    }
    */
}
