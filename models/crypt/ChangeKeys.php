<?php


namespace app\models\crypt;


use Yii;
use yii\base\Model;

class ChangeKeys extends Model
{
    private $db = null;

    public $key1;
    public $key2;
    public $key3;

    public function rules()
    {
        return [
            [['key1', 'key2', 'key3'], 'string']
        ];
    }

    /**
     * Сохранение временной части ключа
     * @throws \yii\db\Exception
     */
    public function SaveRecryptKeys()
    {
        if (!empty($this->key1) && UserKeyLk::accessKey1()) {
            //ключ1
            Yii::$app->db->createCommand()->delete('keys', '`ID`=10')->execute();
            Yii::$app->db->createCommand()->insert('keys', [
                'ID' => 10,
                'Value' => $this->key1
            ])->execute();
        }

        if (!empty($this->key2) && UserKeyLk::accessKey2()) {
            //ключ2
            Yii::$app->db->createCommand()->delete('keys', '`ID`=11')->execute();
            Yii::$app->db->createCommand()->insert('keys', [
                'ID' => 11,
                'Value' => $this->key2
            ])->execute();
        }

        if (!empty($this->key3) && UserKeyLk::accessKey3()) {
            //ключ3
            Yii::$app->db->createCommand()->delete('keys', '`ID`=12')->execute();
            Yii::$app->db->createCommand()->insert('keys', [
                'ID' => 12,
                'Value' => $this->key3
            ])->execute();
        }

        return 1;

    }

    /**
     * Замена ключей шифрования карт
     *
     * @return int
     * @throws \yii\db\Exception
     */
    public function ReencrypKards()
    {
        $this->db = Yii::$app->db;

        $this->key1 = Yii::$app->db->createCommand('SELECT `Value` FROM `keys` WHERE `ID`=10')->queryScalar();
        $this->key2 = Yii::$app->db->createCommand('SELECT `Value` FROM `keys` WHERE `ID`=11')->queryScalar();
        $this->key3 = Yii::$app->db->createCommand('SELECT `Value` FROM `keys` WHERE `ID`=12')->queryScalar();

        if (empty($this->key1) || empty($this->key2) || empty($this->key3)) {
            return 0;
        }

        $sql = "
            SELECT
                `ID`,
                `EncryptedKeyValue`
            FROM
                `crypto_keys_table`                    
        ";
        $result = $this->db->createCommand($sql)->query();
        if ($result->rowCount > 0) {

            $tr = $this->db->beginTransaction();

            $newKek = hash('sha256', $this->key1 . $this->key2 . $this->key3);
            $newKek = substr($newKek, 0, strlen($this->key1 . $this->key2 . $this->key3));

            $tokenizer = new Tokenizer();
            $oldKek = $tokenizer->GetKek();
            if (empty($oldKek)) {
                Yii::warning("Old Kek fail!");
                return 0;
            }

            $crypt = new Crypt();

            while ($row = $result->read()) {
                //расшифровать ключ старым паролем
                $decOld = $crypt->decrypt($row['EncryptedKeyValue'], $oldKek);
                if ($decOld === false) {
                    Yii::warning("Decrypt EncryptedKeyValue fail! id=".$row['ID']);
                    return 0;
                }
                //новый пароль
                $decNew = hash('sha256', openssl_random_pseudo_bytes(64));
                //расшифровать все карты старого пароля и зашифровать новым
                $sql = "
                    SELECT
                        `ID`,
                        `EncryptedPAN`
                    FROM
                        `pan_token`
                    WHERE
                        `CryptoKeyId` = :ID
                ";
                $resPan = $this->db->createCommand($sql, [':ID' => $row['ID']])->query();
                if ($resPan->rowCount > 0) {
                    while ($rowPan = $resPan->read()) {
                        $cardPan = $crypt->decrypt($rowPan['EncryptedPAN'], $decOld);
                        if ($decOld === false) {
                            Yii::warning("Decrypt EncryptedPAN fail! id=".$rowPan['ID']);
                            return 0;
                        }
                        $newPan = $crypt->encrypt($cardPan, $decNew);
                        $sql = "
                            UPDATE
                                `pan_token`
                            SET
                                `EncryptedPAN` = :NEWPAN
                            WHERE
                                `ID` = :ID
                        ";
                        $this->db->createCommand($sql, [':NEWPAN' => $newPan, ':ID' => $rowPan['ID']])->execute();
                    }
                }

                //зашифровать новый пароль и сохранить в БД
                $newEncryptedDec = $crypt->encrypt($decNew, $newKek);

                $sql = "
                    UPDATE
                        `crypto_keys_table`
                    SET
                        `EncryptedKeyValue` = :NEWDEC
                    WHERE
                        `ID` = :ID
                ";
                $this->db->createCommand($sql, [':NEWDEC' => $newEncryptedDec, ':ID' => $row['ID']])->execute();
            }

            //удалить временные ключи
            Yii::$app->db->createCommand()->delete('keys', '`ID`=10')->execute();
            Yii::$app->db->createCommand()->delete('keys', '`ID`=11')->execute();
            Yii::$app->db->createCommand()->delete('keys', '`ID`=12')->execute();

            $tr->commit();

            Yii::warning("ReencrypKards done");
            return 1;
        }
        return 0;
    }

}