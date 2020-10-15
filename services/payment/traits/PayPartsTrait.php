<?php


namespace app\services\payment\traits;


use app\models\bank\TCBank;
use app\models\bank\TcbGate;
use app\models\partner\admin\VyvodParts;
use app\models\payonline\CreatePay;
use app\models\payonline\Partner;
use app\models\payonline\Provparams;
use app\models\payonline\Uslugatovar;
use app\models\PayschetPart;
use app\models\Payschets;
use app\models\TU;
use Carbon\Carbon;
use Yii;
use yii\db\Query;

trait PayPartsTrait
{
    /**
     *
     */
    public function sendPartsToPartners()
    {
        $dateFrom = Carbon::now()->addDays(-1)->startOfDay();
        $dateTo = Carbon::now()->startOfDay();

        $senderPartners = $this->getPartsSenderPartners($dateFrom, $dateTo);

        /** @var Partner $senderPartner */
        foreach ($senderPartners as $senderPartner) {
            $recipientPartners = $this->getPartsRecipientPartners($senderPartner, $dateFrom, $dateTo);

            /** @var Partner $recipientPartner */
            foreach ($recipientPartners as $recipientPartner) {
                $this->payPartsSenderToRecipient($senderPartner, $recipientPartner, $dateFrom, $dateTo);
            }
        }
    }

    /**
     * @param Partner $senderPartner
     * @param Partner $recipientPartner
     * @param Carbon $dateFrom
     * @param Carbon $dateTo
     * @return boolean
     * @throws \yii\db\Exception
     */
    private function payPartsSenderToRecipient(Partner $senderPartner, Partner $recipientPartner, Carbon $dateFrom, Carbon $dateTo)
    {
        $transaction = Yii::$app->db->beginTransaction();

        $transactionOk = true;
        try {
            $vyvodParts = new VyvodParts();
            $vyvodParts->SenderId = $senderPartner->ID;
            $vyvodParts->RecipientId = $recipientPartner->ID;
            $vyvodParts->PayschetId = 0;
            $vyvodParts->Amount = 0;
            $vyvodParts->DateCreate = Carbon::now()->timestamp;
            $vyvodParts->Status = VyvodParts::STATUS_CREATED;
            $transactionOk &= $vyvodParts->save(false);

            $data = PayschetPart::find()
                ->innerJoin('pay_schet', 'pay_schet.ID = pay_schet_parts.PayschetId')
                ->where([
                    'pay_schet_parts.VyvodId' => 0,
                    'pay_schet.Status' => 1,
                    'pay_schet_parts.PartnerId' => $recipientPartner->ID,
                    'pay_schet.IdOrg' => $senderPartner->ID,

                ])
                ->andWhere(['=', 'pay_schet.IdOrg', $senderPartner->ID])
                ->andWhere(['>=', 'pay_schet.DateCreate', $dateFrom->timestamp])
                ->andWhere(['<=', 'pay_schet.DateCreate', $dateTo->timestamp])
                ->all();

            /** @var PayschetPart $row */
            foreach ($data as $payschetPart) {
                $vyvodParts->Amount += $payschetPart->Amount;
            }

            $usl = Uslugatovar::findOne([
                'IDPartner' => $senderPartner->ID,
                'IsCustom' => TU::$VYVODPAYSPARTS,
            ]);

            if(!$usl) {
                Yii::warning("VyvodParts: error mfo=" . $senderPartner->ID . " У получателя нет услуги перечисления разбивки ", 'pay-parts');
                $transaction->rollBack();
                return false;
            }

            // TODO: multibank
            $descript = sprintf(
                'Перечисление сумм разбивок для %s (%d) за %s',
                $recipientPartner->Name,
                $recipientPartner->ID,
                $dateFrom->locale('ru')->format('d-m-Y')
            );
            $pay = new CreatePay();
            $Provparams = new Provparams;
            $Provparams->prov = $usl;

            if(!$recipientPartner->partner_bank_rekviz) {
                $transaction->rollBack();
                throw new \Exception('У партнера-получателя нет реквизитов');
            }

            $Provparams->param = [
                $recipientPartner->partner_bank_rekviz[0]->RaschShetPolushat,
                $recipientPartner->partner_bank_rekviz[0]->BIKPoluchat,
                $recipientPartner->partner_bank_rekviz[0]->NamePoluchat,
                $recipientPartner->partner_bank_rekviz[0]->INNPolushat,
                $recipientPartner->partner_bank_rekviz[0]->KPPPoluchat,
                $descript,
            ];
            $Provparams->summ = $vyvodParts->Amount;
            $Provparams->Usluga = $usl;

            $idpay = $pay->createPay($Provparams,0, 3, TCBank::$bank, $senderPartner->ID, 'vozparts '. $vyvodParts->Id, 0);
            if (!$idpay) {
                Yii::warning("VyvodParts: error mfo=" . $senderPartner->ID . " idpay=" . $idpay, 'pay-parts');
                $transaction->rollBack();
                return false;
            }

            $vyvodParts->PayschetId = $idpay['IdPay'];
            $transactionOk &= $vyvodParts->save(false);

            if($transactionOk) {
                $transaction->commit();
            } else {
                $transaction->rollBack();
            }

        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }

        Yii::warning("VyvodVoznag: mfo=" . $senderPartner->ID . " idpay=" . $idpay, 'pay-parts');

        $TcbGate = new TcbGate($senderPartner->ID,TCBank::$PARTSGATE);
        $bank = new TCBank($TcbGate);
        $ret = $bank->transferToAccount([
            'IdPay' => $vyvodParts->PayschetId,
            'account' => $recipientPartner->partner_bank_rekviz[0]->RaschShetPolushat,
            'bic' => $recipientPartner->partner_bank_rekviz[0]->BIKPoluchat,
            'summ' => $vyvodParts->Amount,
            'name' => $recipientPartner->partner_bank_rekviz[0]->NamePoluchat,
            'inn' => $recipientPartner->partner_bank_rekviz[0]->INNPolushat,
            'descript' => $descript
        ]);

        if ($ret && $ret['status'] == 1) {
            //сохранение номера транзакции
            $payschets = new Payschets();
            $payschets->SetBankTransact([
                'idpay' => $vyvodParts->PayschetId,
                'trx_id' => $ret['transac'],
                'url' => ''
            ]);

            Yii::warning("VyvodParts: mfo=" . $senderPartner->ID . ", transac=" . $ret['transac'], 'pay-parts');

            $payschets->confirmPay([
                'idpay' => $vyvodParts->PayschetId,
                'result_code' => 1,
                'trx_id' => $ret['transac'],
                'ApprovalCode' => '',
                'RRN' => '',
                'message' => ''
            ]);

            $vyvodParts->Status = VyvodParts::STATUS_COMPLETED;
            $vyvodParts->save();
        } else {
            //не вывелось
            $vyvodParts->Status = VyvodParts::STATUS_ERROR;
            $vyvodParts->save();
        }

        /** @var PayschetPart $row */
        foreach ($data as $payschetPart) {
            $payschetPart->VyvodId = $vyvodParts->Id;
            $payschetPart->save();
        }
        return true;
    }

    /**
     * @param Carbon $dateFrom
     * @param Carbon $dateTo
     * @return Partner[]
     */
    private function getPartsSenderPartners(Carbon $dateFrom, Carbon $dateTo)
    {
        $query = new Query();
        $partnerSenderIds = $query
            ->select([
                'p.IdOrg',
            ])
            ->from('pay_schet_parts AS pp')
            ->innerJoin('pay_schet AS p', 'p.ID = pp.PayschetId')
            ->where([
                'pp.VyvodId' => 0,
                'p.Status' => 1,
            ])
            ->andWhere(['>=', 'p.DateCreate', $dateFrom->timestamp])
            ->andWhere(['<=', 'p.DateCreate', $dateTo->timestamp])
            ->groupBy('p.IdOrg')
            ->column();

        $result = Partner::find()->where(['in', 'ID', $partnerSenderIds])->all();
        return $result;
    }

    /**
     * @param $partnerId
     * @param Carbon $dateFrom
     * @param Carbon $dateTo
     * @return Partner[]
     */
    private function getPartsRecipientPartners($partner, Carbon $dateFrom, Carbon $dateTo)
    {
        $query = new Query();
        $partnerRecipientIds = $query
            ->select([
                'pp.PartnerId',
            ])
            ->from('pay_schet_parts as pp')
            ->innerJoin('pay_schet as p', 'p.ID = pp.PayschetId')
            ->where([
                'pp.VyvodId' => 0,
                'p.Status' => 1,
                'p.IdOrg' => $partner->ID,
            ])
            ->andWhere(['>=', 'p.DateCreate', $dateFrom->timestamp])
            ->andWhere(['<=', 'p.DateCreate', $dateTo->timestamp])
            ->groupBy('pp.PartnerId')
            ->column();

        $result = Partner::find()->where(['in', 'ID', $partnerRecipientIds])->all();
        return $result;
    }

}