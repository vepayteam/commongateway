<?php


namespace app\models\antifraud\support_objects;


use app\models\antifraud\tables\AFFingerPrit;
use app\models\payonline\Cards;

class TransInfo
{
    private $user_hash;
    private $card_num;
    private $trans_id;
    private $finger_id;

    public function __construct(string $user_hash, string $card_num, int $trans_id)
    {
        $this->card_num = $card_num;
        $this->user_hash = $user_hash;
        $this->trans_id = $trans_id;
    }

    public function card_hash(): string
    {
        if ($this->card_num) {
            return md5(Cards::MaskCard($this->card_num));
        }
        return '';
    }

    public function bin_card(): string
    {
        if ($this->card_num) {
            return substr($this->card_num, 0, 6);
        }
        return '';
    }

    public function user_hash(): string
    {
        if ($this->user_hash) {
            return $this->user_hash;
        }
        return '';
    }

    public function trans_id(): int
    {
        if ($this->trans_id) {
            return $this->trans_id;
        }
        return 0;
    }

    public function finger_id()
    {
        if (is_null($this->finger_id)) {
            $record = AFFingerPrit::find()
                ->where([
                    'user_hash' => $this->user_hash,
                    'transaction_id' => $this->trans_id,
                ])
                ->one();
            if (!$record){
                $this->finger_id = -1;
            }else{
                $this->finger_id = $record->id;
            }
        }
        return $this->finger_id;
    }
}