<?php

namespace app\models\mfo;

use app\models\Crypt;
use app\models\crypt\CardToken;
use app\models\payonline\User;
use app\models\Payschets;
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
     * @param string $card
     * @param User $user
     * @return string
     * @throws \yii\db\Exception
     */
    private function SaveOutard($IdPay, $card, User $user)
    {
        $CardToken = new CardToken();
        $IdPan = $CardToken->CreateToken($card, 0, '');
        $maskedPan = $this->MaskedCardNumber($card);
        if ($IdPan) {
            Yii::warning('register out card: ' . $maskedPan . ' IdPay=' . $IdPay . " IdPan=".$IdPan);
            Yii::$app->db->createCommand()
                ->insert('cards', [
                    'IdUser' => $user->ID,
                    'NameCard' => $maskedPan,
                    'CardNumber' => $maskedPan,
                    'ExtCardIDP' => $IdPan,
                    'DateAdd' => time(),
                    'SrokKard' => 0,
                    'Status' => 1,
                    'TypeCard' => 1,
                    'IdPan' => $IdPan
                ])->execute();

            $IdCard = Yii::$app->db->getLastInsertID();
            Yii::$app->db->createCommand()
                ->update('pay_schet', [
                    'IdKard' => $IdCard,
                    //'Status' => 2
                ], 'ID = :IDPAY', [':IDPAY' => $IdPay]
                )->execute();

            return $IdCard;

        } else {
            Yii::warning('erorr PAN CreateToken! IdPay='.$IdPay);
            return 0;
        }
    }

    public function MaskedCardNumber($card)
    {
        if (!empty($card)) {
            return substr($card, 0, 6)."******".substr($card, strlen($card) - 4, 4);
        } else {
            return "";
        }
    }
};