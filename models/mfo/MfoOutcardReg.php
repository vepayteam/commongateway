<?php

namespace app\models\mfo;

use app\helpers\TokenHelper;
use app\models\api\Reguser;
use app\models\crypt\CardToken;
use app\models\payonline\Cards;
use app\models\payonline\User;
use app\models\Payschets;
use app\services\cards\models\PanToken;
use app\services\payment\exceptions\CardTokenException;
use app\services\payment\models\Bank;
use app\services\payment\models\PaySchet;
use Yii;

class MfoOutcardReg
{
    /**
     * @param $IdPay
     * @param int $result
     * @param string $cardNumber
     * @param $user
     * @param $org
     * @return int|string
     * @throws \yii\db\Exception
     * @throws \Exception
     */
    public function SaveCard($IdPay, $result, $cardNumber)
    {
        $payschets = new Payschets();
        //данные счета для оплаты
        $params = $payschets->getSchetData($IdPay);

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
                    $IdCard = $this->SaveOutard($IdPay, $cardNumber);
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
    private function SaveOutard($IdPay, $cardNumber)
    {
        $paySchet = PaySchet::findOne($IdPay);
        $partner = $paySchet->partner;

        $token = TokenHelper::getOrCreateToken($cardNumber, null, null);
        if ($token === null) {
            Yii::error('Unable to create token! IdPay=' . $IdPay);
            return 0;
        }
        $panToken = PanToken::findOne($token);

        $maskedCardNumber = $panToken->FirstSixDigits . '******' . $panToken->LastFourDigits;
        Yii::warning("Register out card: {$maskedCardNumber} IdPay={$IdPay} IdPan={$panToken->ID}");

        $card = Cards::find()
            ->alias('cardAlias')
            ->notSoftDeleted()
            ->joinWith([
                /** @see Cards::$user */
                'user userAlias',
            ])
            ->andWhere([
                'cardAlias.IdPan' => $panToken->ID,
                'cardAlias.IdBank' => Bank::OUT_BANK_ID,
                'cardAlias.TypeCard' => Cards::TYPE_CARD_OUT,
                'userAlias.ExtOrg' => $paySchet->IdOrg,
            ])
            ->orderBy(['cardAlias.ID' => SORT_DESC])
            ->limit(1) // optimization
            ->one();


        if ($card === null) {
            $user = (new Reguser())->findUser(
                '0',
                $partner->ID . '-' . time() . random_int(100, 999),
                md5($partner->ID . '-' . time()),
                $partner->ID,
                false
            );

            $card = new Cards();
            $card->IdUser = $user->ID;
            $card->CardNumber = $card->NameCard = $maskedCardNumber;

            /** @todo Check and remove (change to 0). */
            $card->ExtCardIDP = $panToken->ID;

            $card->SrokKard = 0;
            $card->Status = Cards::STATUS_ACTIVE;
            $card->TypeCard = Cards::TYPE_CARD_OUT;
            $card->IdPan = $panToken->ID;
            $card->save(false);
        }

        $paySchet->IdKard = $card->ID;
        $paySchet->IdUser = $card->IdUser;
        $paySchet->save(false);

        return $card->ID;
    }
};