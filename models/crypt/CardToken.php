<?php

namespace app\models\crypt;

class CardToken
{
    /**
     * Создать токен карты
     * @param string $CardNumber
     * @param integer $SrokKard
     * @return integer
     */
    public function CreateToken($CardNumber, $SrokKard)
    {
        $tokenizer = new Tokenizer();
        $token = $tokenizer->CreateToken($CardNumber, $SrokKard);

        return $token;
    }

    /**
     * Номер карты по токену
     * @param integer $token
     * @return string|null
     */
    public function GetCardByToken($token)
    {
        $tokenizer = new Tokenizer();
        $cardPan = $tokenizer->GetCardByToken($token);

        return $cardPan;
    }

    /**
     * Найти существующий токен по карте
     * @param integer $token
     * @return integer
     */
    public function CheckExistToken($CardNumber, $SrokKard)
    {
        $tokenizer = new Tokenizer();
        $token = $tokenizer->CheckExistToken($CardNumber, $SrokKard);

        return $token;
    }
}