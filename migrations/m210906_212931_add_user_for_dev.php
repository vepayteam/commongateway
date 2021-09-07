<?php

class m210906_212931_add_user_for_dev extends \yii\db\Migration
{
    public function safeUp()
    {
        $this->execute("
INSERT INTO partner_users (Login, Password, IsAdmin, RoleUser, IdPartner, FIO, Email, Doljnost, IsActive, IsDeleted, DateLastLogin, ErrorLoginCnt, DateErrorLogin, AutoLockDate)
VALUES ('kbolshakov2', '1c19820a2c4677e50c8f4415a3ea7cf22a364c1259d303660b04e8d4c734412f', 1, 1, 0, 'kbolshakov2', 'kbolshakov2@***.online', NULL, 1, 0, 1830960917, 0, 0, 0);");
    }

    public function safeDown()
    {
        $this->execute("DELETE FROM partner_users WHERE Login='kbolshakov2'");
    }
}
