<?php


namespace app\models\crypt;

use Yii;
use yii\base\Model;

class InsertKey extends Model
{
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
     * Сохранение ключа в БД
     *
     * @return int
     * @throws \yii\db\Exception
     */
    public function SaveKek1()
    {
        if (!empty($this->key1)) {
            $db = Yii::$app->db;

            if ($db) {
                $db->createCommand()->delete('keys', '`ID` = 1')->execute();
                $db->createCommand()->insert('keys', ['ID' => 1, 'Value' => $this->key1])->execute();

                Yii::warning("SaveKek1 done");
                return 1;
            }
        }

        return 0;
    }

    /**
     * Сохранение ключа в Файл
     *
     * @return int
     * @throws \yii\db\Exception
     */
    public function SaveKek2()
    {
        if (!empty($this->key2)) {
            $fp = fopen(Yii::$app->basePath . '/key/key.txt', 'w');
            fwrite($fp, $this->key2);
            fclose($fp);
            Yii::warning("SaveKek2 done");
            return 1;
        }
        return 0;
    }

    /**
     * Сохранение ключа в ОЗУ
     *
     * @return int
     * @throws \yii\db\Exception
     */
    public function SaveKek3()
    {
        if (!empty($this->key3)) {

            $shmKey = ftok(Yii::$app->basePath."/yii", 't');

            if (@$shm_id = shmop_open($shmKey, 'a', 0600, 255))
                @shmop_delete($shm_id);

            $mp = shmop_open($shmKey, 'c', 0600, 255);
            if ($mp) {
                shmop_write($mp, $this->key3, 0);
                shmop_close($mp);

                Yii::warning("SaveKek3 done");
                return 1;
            }

        }

        return 0;
    }

}