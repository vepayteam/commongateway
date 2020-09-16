<?php

use yii\db\Migration;

/**
 * Class m200916_102128_add_more_indexes_stat
 */
class m200916_102128_add_more_indexes_stat extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createIndex(
            "idx_pay_schet_datecreate",
            "pay_schet",
            "DateCreate"
        );
        $this->createIndex(
            "idx_pay_schet_sms_accept",
            "pay_schet",
            "sms_accept"
        );
        $this->createIndex(
            "idx_pay_schet_idorg",
            "pay_schet",
            "IdOrg"
        );
        $this->createIndex('idx_vy_par_tp', 'vyvod_system', ['IdPartner', 'TypeVyvod']);
        $this->createIndex('idx_vy_par_tp_dt', 'vyvod_system', ['IdPartner', 'TypeVyvod', 'DateOp']);
        $this->createIndex("idx_vyvod_dateto", "vyvod_system", "DateTo");
        $this->createIndex("idx_out_dateop", "partner_orderout", "DateOp");
        $this->createIndex("idx_in_dateop", "partner_orderin", "DateOp");
        $this->createIndex('idx_stmnt_par_dtpp_tp', 'statements_account', ['IdPartner', 'DatePP', 'TypeAccount']);
        $this->createIndex('idx_stmnt_pln_par_tpacc', 'statements_planner', ['IdPartner', 'IdTypeAcc']);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex(
            "idx_pay_schet_datecreate",
            "pay_schet"
        );
        $this->dropIndex(
            "idx_pay_schet_sms_accept",
            "pay_schet"
        );
        $this->dropIndex(
            "idx_pay_schet_idorg",
            "pay_schet"
        );
        $this->dropIndex('idx_vy_par_tp', 'vyvod_system');
        $this->dropIndex('idx_vy_par_tp_dt', 'vyvod_system');
        $this->dropIndex("idx_vyvod_dateto", "vyvod_system");
        $this->dropIndex("idx_out_dateop", "partner_orderout");
        $this->dropIndex("idx_in_dateop", "partner_orderin");
        $this->dropIndex('idx_stmnt_par_dtpp_tp', 'statements_account');
        $this->dropIndex('idx_stmnt_pln_par_tpacc', 'statements_planner');
    }
}
