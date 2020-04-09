<?php


namespace app\models\crypt;

use Yii;
use yii\base\Model;

class InitKeys extends Model
{
    private $db;
    public $cntkeys;

    public function rules()
    {
        return [
            [['cntkeys'], 'integer'],
            [['cntkeys'], 'required']
        ];
    }

    public function CreateKeys()
    {
        //учетная запись смены ключей
        $this->db = Yii::$app->db;

        $tokenizer = new Tokenizer();
        $kek = $tokenizer->GetKek();
        if (empty($kek) || !$this->db) {
            return 0;
        }

        $tr = $this->db->beginTransaction();
        for ($i = 0; $i < $this->cntkeys; $i++) {
            $key = hash('sha256', openssl_random_pseudo_bytes(64));
            $crypt = new Crypt();
            $deckey = $crypt->encrypt($key, $kek);
            if (empty($deckey)) {
                return 0;
            }

            $this->db->createCommand()->insert('crypto_keys_table', [
                'EncryptedKeyValue' => $deckey,
                'CreatedDate' => time(),
                'UpdatedDate' => time(),
                'Counter' => 0
            ])->execute();
        }

        $tr->commit();

        Yii::warning("CreateKeys done");
        return 1;
    }
}