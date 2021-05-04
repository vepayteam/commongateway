<?php


namespace app\services\payment\traits;


use app\models\crypt\Tokenizer;
use app\models\payonline\Cards;
use app\services\payment\models\PayCard;
use app\services\payment\models\PaySchet;
use Yii;

trait CardsTrait
{
    /**
     * @param PaySchet $paySchet
     * @param PayCard $payCard
     * @return bool
     * @throws \yii\db\Exception
     */
    public function tokenizeCard(PaySchet $paySchet, PayCard $payCard)
    {
        $tokenizer = new Tokenizer();
        $idPan = $tokenizer->CheckExistToken($payCard->number,$payCard->expMonth . $payCard->expYear);
        if (($idPan) == 0) {
            $idPan = $tokenizer->CreateToken($payCard->number, $payCard->expMonth . $payCard->expYear, $payCard->holder);
        }

        if (!$idPan) {
            return false;
        }

        // TODO: refact to models
        $rowCard = Yii::$app->db->createCommand("
            SELECT
                c.ID,
                c.ExtCardIDP
            FROM
                `user` AS u 
                LEFT JOIN `cards` AS c ON(c.IdUser = u.ID AND c.TypeCard = 0)
            WHERE
                u.ID = :IDUSER AND u.IsDeleted = 0
        ", [':IDUSER' => $paySchet->IdUser]
        )->queryOne();

        if ($rowCard) {
            //удалить старую
            Yii::$app->db->createCommand()
                ->update('cards', [
                    'IsDeleted' => 1
                ], 'IdUser = :IDUSER', [':IDUSER' => $paySchet->IdUser])
                ->execute();
        }

        //новая карта
        Yii::$app->db->createCommand()->insert('cards', [
            'IdUser' => $paySchet->IdUser,
            'NameCard' => Cards::MaskCard($payCard->number),
            'ExtCardIDP' => 0,
            'CardNumber' => Cards::MaskCard($payCard->number),
            'CardType' => 0,
            'SrokKard' => $payCard->expMonth . $payCard->expYear,
            'CardHolder' => mb_substr($payCard->holder, 0, 99),
            'Status' => 1,
            'DateAdd' => time(),
            'Default' => 0,
            'IdPan' => $idPan
        ])->execute();

        return true;
    }

    /**
     * @param PaySchet $paySchet
     * @param PayCard $payCard
     * @throws \yii\db\Exception
     */
    public function updateCardExtId(PaySchet $paySchet, PayCard $payCard)
    {
        // TODO: refact to models
        $rowCard = Yii::$app->db->createCommand("
            SELECT
                c.ID,
                c.ExtCardIDP
            FROM
                `user` AS u 
                LEFT JOIN `cards` AS c ON(c.IdUser = u.ID AND c.TypeCard = 0)
            WHERE
                u.ID = :IDUSER AND u.IsDeleted = 0
        ", [':IDUSER' => $paySchet->IdUser]
        )->queryOne();

        if ($rowCard && $rowCard['ID'] > 0) {
            //Карта есть
            if ($rowCard['ExtCardIDP'] != $payCard->bankId) {
                $cardDataToUpdate = [
                    'ExtCardIDP' => $payCard->bankId,
                ];
                /** Rules: @var PayCard */
                if ($payCard->validate()) {
                    array_push($cardDataToUpdate, [
                        'CardNumber' => $payCard->number,
                        'CardType' => $payCard->type,
                        'SrokKard' => $payCard->expMonth . $payCard->expYear,
                        'CardHolder' => mb_substr($payCard->holder, 0, 99),
                        'IdBank' => $paySchet->Bank,
                    ]);
                }
                //обновить данные карты
                Yii::$app->db
                    ->createCommand()
                    ->update('cards', $cardDataToUpdate, '`ID` = :ID', ['ID' => $rowCard['ID']])
                    ->execute();
            }

            Yii::$app->db->createCommand()
                ->update('pay_schet', [
                    'IdKard' => $rowCard['ID'],
                ], 'ID = :IDPAY', [':IDPAY' => $paySchet->ID])
                ->execute();
        } else {
            //новая карта
            $this->saveCard($paySchet, $payCard);
        }
    }

    /**
     * @param PaySchet $paySchet
     * @param PayCard $payCard
     * @return bool
     * @throws \yii\db\Exception
     */
    protected function saveCard(PaySchet $paySchet, PayCard $payCard)
    {
        $rowCard = Yii::$app->db->createCommand("
            SELECT
                c.ID,
                c.ExtCardIDP
            FROM
                `user` AS u 
                LEFT JOIN `cards` AS c ON(c.IdUser = u.ID AND c.TypeCard = 0)
            WHERE
                u.ID = :IDUSER AND u.IsDeleted = 0
        ", [':IDUSER' => $paySchet->IdUser]
        )->queryOne();

        if ($rowCard) {
            //пользователь есть
            if ($rowCard['ExtCardIDP'] != $payCard->bankId) {
                //Карта есть - удалить старую
                Yii::$app->db->createCommand()
                    ->update('cards', [
                        'IsDeleted' => 1
                    ], 'IdUser = :IDUSER', [':IDUSER' => $paySchet->IdUser])
                    ->execute();

                //новая карта
                Yii::$app->db->createCommand()->insert('cards', [
                    'IdUser' => $paySchet->IdUser,
                    'NameCard' => $payCard->number,
                    'ExtCardIDP' => $payCard->bankId,
                    'CardNumber' => $payCard->number,
                    'CardType' => $payCard->type,
                    'SrokKard' => $payCard->expMonth . $payCard->expYear,
                    'CardHolder' => mb_substr($payCard->holder, 0, 99),
                    'Status' => 1,
                    'DateAdd' => time(),
                    'Default' => 0,
                    'IdBank' => $paySchet->Bank,
                ])->execute();
                $IdCart = Yii::$app->db->getLastInsertID();
            } else {
                //Карта есть - вернуть её
                $IdCart = $rowCard['ID'];
            }
        }

        if ($IdCart) {
            Yii::$app->db->createCommand()
                ->update('pay_schet', [
                    'IdKard' => $IdCart,
                ], 'ID = :IDPAY', [':IDPAY' => intval($paySchet->ID)])
                ->execute();
        }
        return true;
    }

}
