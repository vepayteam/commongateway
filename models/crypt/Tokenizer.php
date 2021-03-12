<?php

namespace app\models\crypt;

use Yii;
use yii\helpers\Json;

class Tokenizer
{
    private $db;

    public function __construct()
    {
        $this->db = Yii::$app->db;
    }

    /**
     * Создать токен карты
     * @param string $CardNumber
     * @param integer $SrokKard
     * @param string $CardHolder
     * @return integer
     */
    public function CreateToken($CardNumber, $SrokKard, $CardHolder)
    {
        $EncryptedPan = $this->Crypt($CardNumber, $CryptKeyId);
        if (empty($EncryptedPan)) {
            Yii::warning('CreateToken: empty EncryptedPan');
            return 0;
        }
        try {
            $this->db->createCommand()->insert('pan_token', [
                'FirstSixDigits' => substr($CardNumber, 0, 6),
                'LastFourDigits' => substr($CardNumber, -4, 4),
                'EncryptedPAN' => $EncryptedPan,
                'ExpDateMonth' => substr(sprintf("%04d", $SrokKard), 0, 2),
                'ExpDateYear' => substr(sprintf("%04d", $SrokKard), 2, 2),
                'CreatedDate' => time(),
                'UpdatedDate' => time(),
                'CryptoKeyId' => $CryptKeyId,
                'CardHolder' => !empty($CardHolder) ? mb_substr($CardHolder, 0, 100) : null
            ])->execute();
            $token = $this->db->getLastInsertID();
        } catch (\yii\db\Exception $e) {
            $token = 0;
            Yii::warning('CreateToken: '.$e->getMessage());
        }

        return $token;
    }

    /**
     * Номер карты по токену
     * @param integer $token
     * @return string|null
     */
    public function GetCardByToken($token)
    {
        try {
            $row = $this->db->createCommand("
                SELECT
                    `EncryptedPAN`,
                    `CryptoKeyId`
                FROM
                    `pan_token`
                WHERE
                    `ID` = :ID
            ", [':ID' => $token])->queryOne();
        } catch (\yii\db\Exception $e) {
            Yii::warning('GetCardByToken: '.$e->getMessage());
            $row = null;
        }

        if(empty($row['EncryptedPAN'])) {
            throw new \Exception('Карта просрочена');
        }

        if ($row) {
            return $this->Decrypt($row['EncryptedPAN'], $row['CryptoKeyId']);
        }
        Yii::warning('EncryptedPAN: id '.$token.' not found');

        return null;
    }

    /**
     * Зашифровать номер карты
     * @param $CardPan
     * @param $CryptKeyId
     * @return string
     */
    private function Crypt($CardPan, &$CryptKeyId)
    {
        $kek = $this->GetKek();
        $dek = $this->GetRandomCryptKeyValue($kek, $CryptKeyId);

        if ($kek && $dek) {
            $crypt = new Crypt();
            $EncryptedPAN = $crypt->encrypt($CardPan, $dek);
            return $EncryptedPAN;
        }
        Yii::warning('TokenizerCrypt: empty keys');
        return '';
    }

    /**
     * Расшифровать номер карты
     * @param $EncryptedPAN
     * @param $CryptKeyId
     * @return string
     */
    private function Decrypt($EncryptedPAN, $CryptKeyId)
    {
        $kek = $this->GetKek();
        $dek = $this->GetCryptKeyValue($kek, $CryptKeyId);
        if ($kek && $dek) {
            $crypt = new Crypt();
            $CardPan = $crypt->decrypt($EncryptedPAN, $dek);
            return $CardPan;
        }
        Yii::warning('TokenizerDecrypt: empty keys');
        return '';
    }

    /**
     * Ключ
     * @return string
     */
    public function GetKek()
    {
        $key1 = $this->GetKeyDB();
        if (!$key1) {
            Yii::warning('TokenizerGetKek: empty key1');
            return '';
        }
        $key2 = $this->GetKeyFile();
        if (!$key2) {
            Yii::warning('TokenizerGetKek: empty key2');
            return '';
        }
        $key3 = $this->GetKeyMem();
        if (!$key3) {
            Yii::warning('TokenizerGetKek: empty key3');
            return '';
        }

        $kek = hash('sha256', $key1 . $key2 . $key3);
        $kek = substr($kek, 0, strlen($key1 . $key2 . $key3));
        return $kek;
    }

    /**
     * Слечайный ключ шифрования
     * @param $kek
     * @param $CryptKeyId
     * @return bool|string|null
     */
    private function GetRandomCryptKeyValue($kek, &$CryptKeyId)
    {
        try {
            $row = $this->db->createCommand("
                SELECT
                    `ID`,
                    `EncryptedKeyValue`,
                    `Counter`
                FROM 
                    `crypto_keys_table` 
                WHERE 
                    `Counter` < 1000
                ORDER BY RAND()
                LIMIT 1
            ")->queryOne();
        } catch (\yii\db\Exception $e) {
            $row = null;
            Yii::warning('GetRandomCryptKeyValue: ' . $e->getMessage());
        }

        if ($row) {
            $CryptKeyId = $row['ID'];

            $this->db->createCommand()->update('crypto_keys_table', [
                'Counter' => $row['Counter'] + 1
            ], '`ID` = :ID', [':ID' => $row['ID']])->execute();

            $crypt = new Crypt();
            return $crypt->decrypt($row['EncryptedKeyValue'], $kek);
        }

        return null;
    }

    /**
     * Ключ шифрования по ID
     * @param $kek
     * @param $CryptKeyId
     * @return string|null
     */
    private function GetCryptKeyValue($kek, $CryptKeyId)
    {
        try {
            $row = $this->db->createCommand("
                SELECT
                    `EncryptedKeyValue`
                FROM 
                    `crypto_keys_table` 
                WHERE 
                    `ID`=:ID
            ", [':ID' => $CryptKeyId])->queryOne();
        } catch (\yii\db\Exception $e) {
            $row = null;
        }

        if ($row) {
            $crypt = new Crypt();
            return $crypt->decrypt($row['EncryptedKeyValue'], $kek);
        }

        Yii::warning('GetCryptKeyValue: key '.$CryptKeyId.' not found');

        return null;
    }

    /**
     * Прочитать ключ из паммяти
     * @return bool|string
     */
    private function GetKeyMem()
    {
        if (Yii::$app->params['DEVMODE'] == 'Y' || Yii::$app->params['TESTMODE'] == 'Y') {
            return '1234567890';
        } else {
            $shmKey = ftok(Yii::$app->basePath . "/yii", 't');
            $mp = shmop_open($shmKey, 'a', 0600, 255);
            if ($mp) {
                $key = trim(shmop_read($mp, 0, 255));
                shmop_close($mp);
                return $key;
            }

            return false;
        }
    }

    /**
     * Прочитать ключ из базы
     * @return bool|string
     */
    private function GetKeyDB()
    {
        $db = Yii::$app->db;

        try {
            return $db->createCommand("SELECT `Value` FROM `keys` WHERE `ID` = 1")->queryScalar();
        } catch (\yii\db\Exception $e) {
            return false;
        }
    }

    /**
     * Прочитать ключ из файла
     * @return bool|string
     */
    private function GetKeyFile()
    {
        if (file_exists(Yii::$app->basePath . '/key/key.txt')) {
            return @file_get_contents(Yii::$app->basePath . '/key/key.txt');
        } else {
            return false;
        }
    }

    /**
     * Тест Ключей
     * @return array
     */
    public function TestKek()
    {
        $key1 = $this->GetKeyDB();
        $key2 = $this->GetKeyFile();
        $key3 = $this->GetKeyMem();

        $kek1Res = !$key1 ? 0 : 1;
        $kek2Res = !$key2 ? 0 : 1;
        $kek3Res = !$key3 ? 0 : 1;

        if ($kek1Res && $kek2Res && $kek3Res) {
            $kek = $this->GetKek();
            $dek = $this->GetCryptKeyValue($kek, mt_rand(1, 5));
            $decRes = !$dek ? 0 : 1;
        } else {
            $decRes = 0;
        }

        $count = $this->db->createCommand("
            SELECT
                COUNT(*)
            FROM
                `crypto_keys_table`                    
        ")->queryScalar();

        $countwork = $this->db->createCommand("
            SELECT
                COUNT(*)
            FROM
                `crypto_keys_table`
            WHERE
                `Counter` < 1000
        ")->queryScalar();

        return ['kek1' => $kek1Res, 'kek2' => $kek2Res, 'kek3' => $kek3Res, 'crypt' => $decRes, 'count' => $count, 'countwork' => $countwork];
    }

    /**
     * Проверка что карта уже есть в БД
     * @param $CardNumber
     * @param $SrokKard
     * @return integer
     */
    public function CheckExistToken($CardNumber, $SrokKard)
    {
        try {
            $res = $this->db->createCommand("
                SELECT
                    `ID`,
                    `EncryptedPAN`,
                    `CryptoKeyId`
                FROM
                    `pan_token`
                WHERE
                    `FirstSixDigits` = :FIRSTDIG
                    AND `LastFourDigits` = :LASTDIG
                    AND `ExpDateMonth` = :EXPMON
                    AND `ExpDateYear` = :EXPYEAR
            ", [
                ':FIRSTDIG' => mb_substr($CardNumber, 0, 6),
                ':LASTDIG' => mb_substr($CardNumber, -4, 4),
                ':EXPMON' => mb_substr(sprintf("%04d", $SrokKard), 0, 2),
                ':EXPYEAR' => mb_substr(sprintf("%04d", $SrokKard), 2, 2)
            ])->query();
        } catch (\yii\db\Exception $e) {
            Yii::warning('CheckExistToken: '.$e->getMessage());
            $res = null;
        }

        if ($res) {

            while ($row = $res->read()) {
                if ($this->Decrypt($row['EncryptedPAN'], $row['CryptoKeyId']) == $CardNumber) {
                    return $row['ID'];
                }
            }
        }

        return 0;

    }
}
