<?php

use yii\db\Migration;

/**
 * Class m200820_103520_create_safe_fk_on_multiple_tables
 */
class m200820_103520_create_safe_fk_on_multiple_tables extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("SET foreign_key_checks = 0;");
        $this->addForeignKey("fk_act_mfo_idpartner_partner_id", "act_mfo", "IdPartner", "partner", "ID", "NO ACTION", "NO ACTION");
        $this->addForeignKey("fk_act_schet_idpartner_partner_id", "act_schet", "IdPartner", "partner", "ID", "NO ACTION", "NO ACTION");
        $this->addForeignKey("fk_alarms_send_idsetting_alarms_settings_id", "alarms_send", "IdSetting", "alarms_settings", "ID", "NO ACTION", "NO ACTION");
        $this->addForeignKey("fk_alarms_send_idpay_pay_schet_id", "alarms_send", "IdPay", "pay_schet", "ID", "NO ACTION", "NO ACTION");
        $this->addForeignKey("fk_cards_iduser_user_id", "cards", "IdUser", "user", "ID", "NO ACTION", "NO ACTION");
        $this->addForeignKey("fk_drafts_idpayschet_pay_schet_id", "drafts", "IdPaySchet", "pay_schet", "ID", "NO ACTION", "NO ACTION");
        $this->addForeignKey("fk_export_pay_idschet_pay_schet_id", "export_pay", "IdSchet", "pay_schet", "ID", "NO ACTION", "NO ACTION");
        $this->addForeignKey("fk_help_iduser_user_id", "help", "IdUser", "user", "ID", "NO ACTION", "NO ACTION");
        $this->addForeignKey("fk_history_iduser_user_id", "history", "IdUser", "user", "ID", "NO ACTION", "NO ACTION");
        $this->addForeignKey("fk_key_log_iduser_user_id", "key_log", "IdUser", "user", "ID", "NO ACTION", "NO ACTION");
        $this->addForeignKey("fk_loglogin_iduser_user_id", "loglogin", "IdUser", "user", "ID", "NO ACTION", "NO ACTION");
        $this->addForeignKey("fk_newsread_idnews_news_id", "newsread", "IdNews", "news", "ID", "NO ACTION", "NO ACTION");
        $this->addForeignKey("fk_newsread_iduser_user_id", "newsread", "IdUser", "user", "ID", "NO ACTION", "NO ACTION");
        $this->addForeignKey("fk_notification_pay_idpay_pay_schet_id", "notification_pay", "IdPay", "pay_schet", "ID", "NO ACTION", "NO ACTION");
        $this->addForeignKey("fk_oplatatakze_fromuser_user_id", "oplatatakze", "FromUser", "user", "ID", "NO ACTION", "NO ACTION");

        $this->addForeignKey("fk_order_notif_idorder_order_pay_id", "order_notif", "IdOrder", "order_pay", "ID", "NO ACTION", "NO ACTION");
        $this->addForeignKey("fk_order_pay_idpartner_partner_id", "order_pay", "IdPartner", "partner", "ID", "NO ACTION", "NO ACTION");
        $this->addForeignKey("fk_order_pay_idpayschet_pay_schet_id", "order_pay", "IdPaySchet", "pay_schet", "ID", "NO ACTION", "NO ACTION");
        $this->addForeignKey("fk_otchetps_idpartner_partner_id", "otchetps", "IdPartner", "partner", "ID", "NO ACTION", "NO ACTION");
        $this->addForeignKey("fk_partner_bank_rekviz_idpartner_partner_id", "partner_bank_rekviz", "IdPartner", "partner", "ID", "NO ACTION", "NO ACTION");
        $this->addForeignKey("fk_partner_dogovor_idpartner_partner_id", "partner_dogovor", "IdPartner", "partner", "ID", "NO ACTION", "NO ACTION");
        $this->addForeignKey("fk_partner_orderin_idpartner_partner_id", "partner_orderin", "IdPartner", "partner", "ID", "NO ACTION", "NO ACTION");
        $this->addForeignKey("fk_partner_orderout_idpartner_partner_id", "partner_orderout", "IdPartner", "partner", "ID", "NO ACTION", "NO ACTION");
        $this->addForeignKey("fk_partner_sumorder_idpartner_partner_id", "partner_sumorder", "IdPartner", "partner", "ID", "NO ACTION", "NO ACTION");
        $this->addForeignKey("fk_partner_sumorder_idrekviz_partner_bank_rekviz_id", "partner_sumorder", "IdRekviz", "partner_bank_rekviz", "ID", "NO ACTION", "NO ACTION");
        $this->addForeignKey("fk_partner_users_idpartner_partner_id", "partner_users", "IdPartner", "partner", "ID", "NO ACTION", "NO ACTION");
        $this->addForeignKey("fk_pay_bonus_iduser_user_id", "pay_bonus", "IdUser", "user", "ID", "NO ACTION", "NO ACTION");
        $this->addForeignKey("fk_pay_bonus_idpay_pay_schet_id", "pay_bonus", "IdPay", "pay_schet", "ID", "NO ACTION", "NO ACTION");
        $this->addForeignKey("fk_pay_schet_iduser_user_id", "pay_schet", "IdUser", "user", "ID", "NO ACTION", "NO ACTION");
        $this->addForeignKey("fk_pay_schet_idusluga_uslugatovar_id", "pay_schet", "IdUsluga", "uslugatovar", "ID", "NO ACTION", "NO ACTION");
        $this->addForeignKey("fk_pay_schet_idkard_cards_id", "pay_schet", "IdKard", "cards", "ID", "NO ACTION", "NO ACTION");
        $this->addForeignKey("fk_pay_schet_idorder_order_pay_id", "pay_schet", "IdOrder", "order_pay", "ID", "NO ACTION", "NO ACTION");

        $this->addForeignKey("fk_statements_account_idpartner_partner_id", "statements_account", "IdPartner", "partner", "ID", "NO ACTION", "NO ACTION");
        $this->addForeignKey("fk_user_address_iduser_user_id", "user_address", "IdUser", "user", "ID", "NO ACTION", "NO ACTION");
        $this->addForeignKey("fk_user_car_iduser_user_id", "user_car", "IdUser", "user", "ID", "NO ACTION", "NO ACTION");
        $this->addForeignKey("fk_user_favor_uslug_iduser_user_id", "user_favor_uslug", "IdUser", "user", "ID", "NO ACTION", "NO ACTION");
        $this->addForeignKey("fk_user_favor_uslug_iduslug_uslugatovar_id", "user_favor_uslug", "IdUslug", "uslugatovar", "ID", "NO ACTION", "NO ACTION");
        $this->addForeignKey("fk_user_identification_iduser_user_id", "user_identification", "IdUser", "user", "ID", "NO ACTION", "NO ACTION");
        $this->addForeignKey("fk_uslugatovar_idpartner_partner_id", "uslugatovar", "IDPartner", "partner", "ID", "NO ACTION", "NO ACTION");
        $this->addForeignKey("fk_uslugatovar_idmagazin_partner_dogovor_id", "uslugatovar", "IdMagazin", "partner_dogovor", "ID", "NO ACTION", "NO ACTION");
        $this->addForeignKey("fk_uslugatovar_idbankrekviz_partner_bank_rekviz_id", "uslugatovar", "IdBankRekviz", "partner_bank_rekviz", "ID", "NO ACTION", "NO ACTION");
        $this->addForeignKey("fk_uslugatovar_group_qr_group_id", "uslugatovar", "Group", "qr_group", "ID", "NO ACTION", "NO ACTION");
        $this->addForeignKey("fk_uslugatovar_region_uslugi_regions_id", "uslugatovar", "Region", "uslugi_regions", "ID", "NO ACTION", "NO ACTION");
        $this->addForeignKey("fk_vyvod_reestr_idpartner_partner_id", "vyvod_reestr", "IdPartner", "partner", "ID", "NO ACTION", "NO ACTION");
        $this->addForeignKey("fk_vyvod_reestr_idpay_pay_schet_id", "vyvod_reestr", "IdPay", "pay_schet", "ID", "NO ACTION", "NO ACTION");
        $this->addForeignKey("fk_vyvod_system_idpartner_partner_id", "vyvod_system", "IdPartner", "partner", "ID", "NO ACTION", "NO ACTION");
        $this->addForeignKey("fk_vyvod_system_idpay_pay_schet_id", "vyvod_system", "IdPay", "pay_schet", "ID", "NO ACTION", "NO ACTION");
        $this->execute("SET foreign_key_checks = 1;");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->execute("SET foreign_key_checks = 0;");
        $this->dropForeignKey("fk_act_mfo_idpartner_partner_id", "act_mfo");
        $this->dropForeignKey("fk_act_schet_idpartner_partner_id", "act_schet");
        $this->dropForeignKey("fk_alarms_send_idsetting_alarms_settings_id", "alarms_send");
        $this->dropForeignKey("fk_alarms_send_idpay_pay_schet_id", "alarms_send");
        $this->dropForeignKey("fk_cards_iduser_user_id", "cards");
        $this->dropForeignKey("fk_drafts_idpayschet_pay_schet_id", "drafts");
        $this->dropForeignKey("fk_export_pay_idschet_pay_schet_id", "export_pay");
        $this->dropForeignKey("fk_help_iduser_user_id", "help");
        $this->dropForeignKey("fk_history_iduser_user_id", "history");
        $this->dropForeignKey("fk_key_log_iduser_user_id", "key_log");
        $this->dropForeignKey("fk_loglogin_iduser_user_id", "loglogin");
        $this->dropForeignKey("fk_newsread_idnews_news_id", "newsread");
        $this->dropForeignKey("fk_newsread_iduser_user_id", "newsread");
        $this->dropForeignKey("fk_notification_pay_idpay_pay_schet_id", "notification_pay");
        $this->dropForeignKey("fk_oplatatakze_fromuser_user_id", "oplatatakze");

        $this->dropForeignKey("fk_order_notif_idorder_order_pay_id", "order_notif");
        $this->dropForeignKey("fk_order_pay_idpartner_partner_id", "order_pay");
        $this->dropForeignKey("fk_order_pay_idpayschet_pay_schet_id", "order_pay");
        $this->dropForeignKey("fk_otchetps_idpartner_partner_id", "otchetps");
        $this->dropForeignKey("fk_partner_bank_rekviz_idpartner_partner_id", "partner_bank_rekviz");
        $this->dropForeignKey("fk_partner_dogovor_idpartner_partner_id", "partner_dogovor");
        $this->dropForeignKey("fk_partner_orderin_idpartner_partner_id", "partner_orderin");
        $this->dropForeignKey("fk_partner_orderout_idpartner_partner_id", "partner_orderout");
        $this->dropForeignKey("fk_partner_sumorder_idpartner_partner_id", "partner_sumorder");
        $this->dropForeignKey("fk_partner_sumorder_idrekviz_partner_bank_rekviz_id", "partner_sumorder");
        $this->dropForeignKey("fk_partner_users_idpartner_partner_id", "partner_users");
        $this->dropForeignKey("fk_pay_bonus_iduser_user_id", "pay_bonus");
        $this->dropForeignKey("fk_pay_bonus_idpay_pay_schet_id", "pay_bonus");
        $this->dropForeignKey("fk_pay_schet_iduser_user_id", "pay_schet");
        $this->dropForeignKey("fk_pay_schet_idusluga_uslugatovar_id", "pay_schet");
        $this->dropForeignKey("fk_pay_schet_idkard_cards_id", "pay_schet");
        $this->dropForeignKey("fk_pay_schet_idorder_order_pay_id", "pay_schet");

        $this->dropForeignKey("fk_statements_account_idpartner_partner_id", "statements_account");
        $this->dropForeignKey("fk_user_address_iduser_user_id", "user_address");
        $this->dropForeignKey("fk_user_car_iduser_user_id", "user_car");
        $this->dropForeignKey("fk_user_favor_uslug_iduser_user_id", "user_favor_uslug");
        $this->dropForeignKey("fk_user_favor_uslug_iduslug_uslugatovar_id", "user_favor_uslug");
        $this->dropForeignKey("fk_user_identification_iduser_user_id", "user_identification");
        $this->dropForeignKey("fk_uslugatovar_idpartner_partner_id", "uslugatovar");
        $this->dropForeignKey("fk_uslugatovar_idmagazin_partner_dogovor_id", "uslugatovar");
        $this->dropForeignKey("fk_uslugatovar_idbankrekviz_partner_bank_rekviz_id", "uslugatovar");
        $this->dropForeignKey("fk_uslugatovar_group_qr_group_id", "uslugatovar");
        $this->dropForeignKey("fk_uslugatovar_region_uslugi_regions_id", "uslugatovar");
        $this->dropForeignKey("fk_vyvod_reestr_idpartner_partner_id", "vyvod_reestr");
        $this->dropForeignKey("fk_vyvod_reestr_idpay_pay_schet_id", "vyvod_reestr");
        $this->dropForeignKey("fk_vyvod_system_idpartner_partner_id", "vyvod_system");
        $this->dropForeignKey("fk_vyvod_system_idpay_pay_schet_id", "vyvod_system");
        $this->execute("SET foreign_key_checks = 1;");
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200820_103520_create_safe_fk_on_multiple_tables cannot be reverted.\n";

        return false;
    }
    */
}
