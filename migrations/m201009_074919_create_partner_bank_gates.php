<?php

use app\models\bank\Banks;
use app\models\payonline\Partner;
use app\services\payment\banks\MTSBankAdapter;
use app\services\payment\banks\TKBankAdapter;
use app\services\payment\models\PartnerBankGate;
use app\services\payment\models\UslugatovarType;
use yii\db\Migration;

/**
 * Class m201009_074919_create_partner_bank_gates
 */
class m201009_074919_create_partner_bank_gates extends Migration
{

    const GATES = [
        'Aft' => [
            UslugatovarType::POGASHATF,
            UslugatovarType::AVTOPLATATF,
        ],
        'Ecom' => [
            UslugatovarType::POGASHECOM,
            UslugatovarType::ECOM,
        ],
        'Oct' => [
            UslugatovarType::TOCARD,
            UslugatovarType::TOSCHET,
        ],
        'Vyvod' => [
            UslugatovarType::VYPLATVOZN,
            UslugatovarType::VYVODPAYS
        ],
        'Jkh' => [
            UslugatovarType::JKH
        ],

        'Auto' => [
            UslugatovarType::REGCARD,
            UslugatovarType::AVTOPLATECOM,
        ],
        'Perevod' => [
            UslugatovarType::REVERSCOMIS,
        ],

        'Parts' => [
            UslugatovarType::ECOMPARTS,
            UslugatovarType::JKHPARTS,
            UslugatovarType::POGASHECOMPARTS,
            UslugatovarType::AVTOPLATATFPARTS,
            UslugatovarType::POGASHATFPARTS,
            UslugatovarType::AVTOPLATECOMPARTS,
        ],
    ];

    public static function getBanksPrefixs()
    {
        return [
            TKBankAdapter::$bank => [
                'Login' => 'LoginTkb',
                'Token' => 'KeyTkb',
                'Password' => null,
            ]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        if ($this->db->getTableSchema(PartnerBankGate::tableName(), true) !== null) {
            $this->dropTable(PartnerBankGate::tableName());
        }

        $this->createTable(PartnerBankGate::tableName(), [
            'Id' => $this->primaryKey(),
            'PartnerId' => $this->integer()->notNull(),
            'BankId' => $this->integer()->notNull(),
            'TU' => $this->integer(),
            'SchetNumber' => $this->string(),
            'Login' => $this->string(),
            'Token' => $this->text(),
            'Password' => $this->text(),
            'Priority' => $this->integer()->defaultValue(0),
            'Enable' => $this->boolean()->defaultValue(true),
        ]);

        foreach (Partner::find()->all() as $partner) {
            $this->addGatesByPartner($partner);
        }

    }

    private function addGatesByPartner(Partner $partner)
    {
        foreach (self::GATES as $gate => $types) {
            foreach ($types as $type) {

                foreach (self::getBanksPrefixs() as $bank => $prefixs) {
                    if($bank == TKBankAdapter::$bank && $gate == 'Auto') {
                        $gate = 'Auto1';
                    }

                    $loginKey = $prefixs['Login'] . $gate;
                    $tokenKey = $prefixs['Token'] . $gate;
                    $passwordKey = $prefixs['Password'] . $gate;
                    if(!empty($partner[$loginKey])) {
                        $partnerBankGate = new PartnerBankGate();
                        $partnerBankGate->PartnerId = $partner->ID;
                        $partnerBankGate->Login = $partner[$loginKey];
                        $partnerBankGate->Token = $partner[$tokenKey];
                        $partnerBankGate->BankId = $bank;
                        $partnerBankGate->TU = $type;

                        if(!is_null($prefixs['Password'])) {
                            $partnerBankGate->Password = $partner[$passwordKey];
                        }
                        $partnerBankGate->save(false);
                    }
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable(PartnerBankGate::tableName());

        return true;
    }
}
