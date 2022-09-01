<?php

namespace app\helpers;

use app\models\crypt\Tokenizer;
use app\services\cards\models\PanToken;

class TokenHelper
{
    /**
     * @param string $cardNumber
     * @param string|null $expires
     * @param string|null $cardholder
     * @return int|null {@see PanToken::$ID}.
     * @todo Move to service.
     */
    public static function getOrCreateToken(string $cardNumber, ?string $expires, ?string $cardholder): ?int
    {
        $tokenizer = new Tokenizer();
        $tokenId = $tokenizer->CheckExistToken($cardNumber, $expires ?? 0);

        if ($tokenId === 0) {
            $tokenId = $tokenizer->CreateToken($cardNumber, $expires ?? 0, $cardholder ?? '');
        }

        return $tokenId !== 0 ? $tokenId : null;
    }

    public static function getCardPanByPanTokenId(int $id): ?string
    {
        $tokenizer = new Tokenizer();
        return $tokenizer->GetCardByToken($id);
    }
}