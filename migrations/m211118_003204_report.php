<?php

use yii\db\Migration;

/**
 * Class m211118_003204_report
 */
class m211118_003204_report extends Migration
{
    private const TABLE_OPTIONS = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';

    /**
     * {@inheritDoc}
     */
    public function safeUp()
    {
        // Transaction report generated upon merchant's request
        $this->createTable('report', [
            'Id' => $this->primaryKey(),
            'PartnerId' => $this->integer()->notNull(),
            'Status' => $this->integer()->notNull()->comment('Report filling up status: 0 - in process, 1 - completed, 2 - error'),
            'Date' => $this->date()->notNull()->comment('Date of transactions which should be in the report'),
            'TransactionStatus' => $this->tinyInteger()->comment('Status of transactions which should be in the report'),
            'CreatedAt' => $this->integer()->notNull()->comment('Creation timestamp'),
            'CompletedAt' => $this->integer()->comment('Timestamp when the report was filled up'),
        ], self::TABLE_OPTIONS);
        $this->addCommentOnTable('report', 'Transaction report generated upon merchant\'s request');


        // Many-to-many relation between report and service type (uslugatovar_types)'
        $this->createTable('report_to_service_type_link', [
            'ReportId' => $this->integer(),
            'ServiceTypeId' => $this->integer()->comment('ID of service type (uslugatovar_types)'),
        ], self::TABLE_OPTIONS);
        $this->addCommentOnTable('report_to_service_type_link', 'Many-to-many relation between report and service type (uslugatovar_types)');
        $this->addPrimaryKey('report_to_service_type_link_pk', 'report_to_service_type_link', ['ReportId', 'ServiceTypeId']);
        $this->addForeignKey(
            'report_to_service_type_link_report_id_fk',
            'report_to_service_type_link', 'ReportId', // from table / field
            'report', 'Id', // to table / field
            'CASCADE', 'CASCADE' // on delete / on update
        );
        $this->addForeignKey(
            'report_to_service_type_link_service_type_id_fk',
            'report_to_service_type_link', 'ServiceTypeId', // from table / field
            'uslugatovar_types', 'Id', // to table / field
            'CASCADE', 'CASCADE' // on delete / on update
        );


        // List of transactions in the corresponding report
        $this->createTable('report_transaction', [
            'Id' => $this->bigPrimaryKey(),
            'ReportId' => $this->integer()->notNull()->comment('Foreign key to the report'),
            'TransactionId' => $this->integer()->notNull()->comment('Transaction (payschet) ID'),
            'Status' => $this->tinyInteger()->notNull()->comment('Transaction (payschet) status'),
            'ErrorCode' => $this->string(10),
            'Error' => $this->string()->comment('Error message'),
            'ExtId' => $this->string()->comment('External ID from merchant'),
            'ProviderBillNumber' => $this->string()->comment('Bill number from payment provider (bank)'),
            'Merchant' => $this->string()->comment('Name of merchant'),
            'ServiceName' => $this->string()->comment('Name of service type (uslugatovar type)'),
            'BasicAmount' => $this->decimal(15, 2)->notNull()->comment('Payment sum'),
            'ClientCommission' => $this->decimal(15, 2)->notNull()->comment('Commission paid by client'),
            'MerchantCommission' => $this->decimal(15, 2)->notNull()->comment('Commission paid by merchant'),
            'Currency' => $this->char(3)->notNull()->comment('Currency code (ISO 4217)'),
            'CardPan' => $this->string()->comment('Masked card number'),
            'CardPaymentSystem' => $this->string()->comment('Payment system name, e.g. Visa, Mastercard, Mir'),
            'Provider' => $this->string()->comment('Payment provider (bank) name'),
            'CreateDateTime' => $this->dateTime(),
            'FinishDateTime' => $this->dateTime(),
        ], self::TABLE_OPTIONS);
        $this->addCommentOnTable('report_transaction', 'List of transactions in the corresponding report');
        $this->addForeignKey(
            'report_transaction_report_id_fk',
            'report_transaction', 'ReportId', // from table / field
            'report', 'Id', // to table / field
            'CASCADE', 'CASCADE' // on delete / on update
        );
    }

    /**
     * {@inheritDoc}
     */
    public function safeDown()
    {
        $this->dropTable('report_transaction');
        $this->dropTable('report_to_service_type_link');
        $this->dropTable('report');
    }
}
