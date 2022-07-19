<?php

namespace app\models\mfo;

use app\models\crypt\CardToken;
use app\models\payonline\Cards;
use app\models\payonline\User;
use app\models\Payschets;
use app\services\cards\models\PanToken;
use app\services\payment\models\Bank;
use app\services\payment\models\PaySchet;
use Yii;

class MfoOutcardReg
{
    /**
     * @param $IdPay
     * @param int $result
     * @param string $card
     * @param $user
     * @param $org
     * @return int|string
     * @throws \yii\db\Exception
     * @throws \Exception
     */
    public function SaveCard($IdPay, $result, $card, User $user, $org)
    {
        $payschets = new Payschets();
        //данные счета для оплаты
        $params = $payschets->getSchetData($IdPay, '', $org);

        if ($params) {
            if ($result > 0) {
                $payschets->confirmPay([
                    'idpay' => $params['ID'],
                    'idgroup' => 0,
                    'result_code' => $result,
                    'trx_id' => $params['ID'],
                    'ApprovalCode' => '',
                    'RRN' => '',
                    'message' => ''
                ]);
                if ($result == 1) {
                    $IdCard = $this->SaveOutard($IdPay, $card, $user);
                    Yii::warning('register out card: '.$IdCard);
                    return $IdCard;
                }
            }
        }

        return 0;
    }

    /**
     * Сохранение карты для выплат
     * @param $IdPay
     * @param string $cardNumber
     * @param User $user
     * @return string
     * @throws \yii\db\Exception
     */
    private function SaveOutard($IdPay, $cardNumber, User $user)
    {
        // find or create the pan token
        $cardToken = new CardToken();
        $panTokenId = $cardToken->CheckExistToken($cardNumber, 0, '');
        if ($panTokenId === 0) {
            $panTokenId = $cardToken->CreateToken($cardNumber, 0, '');
            if ($panTokenId === 0) {
                Yii::error('Unable to create! IdPay=' . $IdPay);
                return 0;
            }
        }
        $panToken = PanToken::findOne($panTokenId);

        $maskedCardNumber = $panToken->FirstSixDigits . '******' . $panToken->LastFourDigits;
        Yii::warning("Register out card: {$maskedCardNumber} IdPay={$IdPay} IdPan={$panToken->ID}");

        $card = Cards::find()
            ->andWhere([
                'IdPan' => $panToken->ID,
                'IdBank' => Bank::OUT_BANK_ID,
                'TypeCard' => Cards::TYPE_CARD_OUT,
            ])
            ->orderBy(['ID' => SORT_DESC])
            ->limit(1) // optimization
            ->one();
        if ($card === null) {
            $card = new Cards();
            $card->IdUser = $user->ID;
            $card->NameCard = $maskedCardNumber;
            $card->CardNumber = $maskedCardNumber;

            /** @todo Check and remove (change to 0). */
            $card->ExtCardIDP = $panToken->ID;

            $card->SrokKard = 0;
            $card->Status = Cards::STATUS_ACTIVE;
            $card->TypeCard = Cards::TYPE_CARD_OUT;
            $card->IdPan = $panToken->ID;
            $card->save(false);
        }

        $paySchet = PaySchet::findOne($IdPay);
        $paySchet->IdKard = $card->ID;
        $paySchet->save(false);

        return $card->ID;
    }
};