<?php


namespace app\services\payment;


use app\models\bank\TCBank;
use app\models\bank\TcbGate;
use app\models\kfapi\KfPay;
use app\models\kfapi\KfRequest;
use app\models\partner\admin\VyvodParts;
use app\models\payonline\CreatePay;
use app\models\payonline\Partner;
use app\models\payonline\PartnerBankRekviz;
use app\models\payonline\Provparams;
use app\models\payonline\Uslugatovar;
use app\models\PayschetPart;
use app\models\Payschets;
use app\models\TU;
use app\services\payment\payment_strategies\CreateFormEcomStrategy;
use app\services\payment\payment_strategies\CreateFormJkhStrategy;
use app\services\payment\payment_strategies\IPaymentStrategy;
use Carbon\Carbon;
use Yii;
use yii\db\Query;
use yii\web\BadRequestHttpException;

class PaymentService
{

    public function createPay(KfRequest $kfRequest)
    {
        /** @var IPaymentStrategy $paymentStrategy */
        $paymentStrategy = null;
        switch($kfRequest->GetReq('type', 0)) {
            case 1:
                $paymentStrategy = new CreateFormJkhStrategy($kfRequest);
                break;
            default:
                $paymentStrategy = new CreateFormEcomStrategy($kfRequest);
                break;
        }


        return $paymentStrategy->exec();
    }


    public function sendPartsToPartners()
    {
        $dateFrom = Carbon::now()->addDays(-1)->startOfDay();
        $dateTo = Carbon::now()->startOfDay();

        $senderPartnerIds = $this->getPartsSenderPartnerIds($dateFrom, $dateTo);

        foreach ($senderPartnerIds as $senderPartnerId) {
            $recipientPartnerIds = $this->getPartsRecipientPartnerIds($senderPartnerId, $dateFrom, $dateTo);
            foreach ($recipientPartnerIds as $recipientPartnerId) {
                $senderPartner = Partner::findOne(['ID' => $senderPartnerId]);
                $recipientPartner = Partner::findOne(['ID' => $recipientPartnerId]);
                $this->payPartsSenderToRecipient($senderPartner, $recipientPartner, $dateFrom, $dateTo);
            }
        }
    }

    private function payPartsSenderToRecipient(Partner $senderPartner, Partner $recipientPartner, Carbon $dateFrom, Carbon $dateTo)
    {
        $tr = Yii::$app->db->beginTransaction();

        $vyvodParts = new VyvodParts();
        $vyvodParts->SenderId = $senderPartner->ID;
        $vyvodParts->RecipientId = $recipientPartner->ID;
        $vyvodParts->PayschetId = 0;
        $vyvodParts->Amount = 0;
        $vyvodParts->DateCreate = Carbon::now()->timestamp;
        $vyvodParts->save();

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
            echo "VyvodParts: error mfo=" . $senderPartner->ID . " У получателя нет услуги перечисления разбивки " . "\r\n";
            $tr->rollBack();
            return 0;
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
        $Provparams->param = [
            $recipientPartner->partner_bank_rekviz[0]->RaschShetPolushat,
            $recipientPartner->partner_bank_rekviz[0]->BIKPoluchat,
            $recipientPartner->partner_bank_rekviz[0]->NamePoluchat,
            $recipientPartner->partner_bank_rekviz[0]->INNPolushat,
            $recipientPartner->partner_bank_rekviz[0]->KPPPoluchat,
            $descript
        ];
        $Provparams->summ = $vyvodParts->Amount;
        $Provparams->Usluga = $usl;

        $idpay = $pay->createPay($Provparams,0, 3, TCBank::$bank, $senderPartner->ID, 'vozparts '. $vyvodParts->Id, 0);
        if (!$idpay) {
            echo "VyvodParts: error mfo=" . $senderPartner->ID . " idpay=" . $idpay . "\r\n";
            $tr->rollBack();
            return 0;
        }

        $vyvodParts->PayschetId = $idpay['IdPay'];
        $vyvodParts->save();

        $tr->commit();

        echo "VyvodVoznag: mfo=" . $senderPartner->ID . " idpay=" . $idpay . "\r\n";

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

            echo "VyvodParts: mfo=" . $senderPartner->ID . ", transac=" . $ret['transac'] . "\r\n";

            //статус не будем смотреть
            $payschets->confirmPay([
                'idpay' => $vyvodParts->PayschetId,
                'result_code' => 1,
                'trx_id' => $ret['transac'],
                'ApprovalCode' => '',
                'RRN' => '',
                'message' => ''
            ]);

            $vyvodParts->Status = 1;
            $vyvodParts->save();
        } else {
            //не вывелось
            $vyvodParts->Status = 2;
            $vyvodParts->save();
        }

        /** @var PayschetPart $row */
        foreach ($data as $payschetPart) {
            $payschetPart->VyvodId = $vyvodParts->Id;
            $payschetPart->save();
        }
        return 1;
    }

    /**
     * @param Carbon $dateFrom
     * @param Carbon $dateTo
     * @return array
     */
    private function getPartsSenderPartnerIds(Carbon $dateFrom, Carbon $dateTo)
    {
        $result = [];

        $query = new Query();
        $data = $query
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
            ->all();

        foreach ($data as $row) {
            $result[] = $row['IdOrg'];
        }
        return $result;
    }

    /**
     * @param $partnerId
     * @param Carbon $dateFrom
     * @param Carbon $dateTo
     * @return array
     */
    private function getPartsRecipientPartnerIds($partnerId, Carbon $dateFrom, Carbon $dateTo)
    {
        $result = [];
        $query = new Query();
        $data = $query
            ->select([
                'pp.PartnerId',
            ])
            ->from('pay_schet_parts as pp')
            ->innerJoin('pay_schet as p', 'p.ID = pp.PayschetId')
            ->where([
                'pp.VyvodId' => 0,
                'p.Status' => 1,
                'p.IdOrg' => $partnerId,
            ])
            ->andWhere(['>=', 'p.DateCreate', $dateFrom->timestamp])
            ->andWhere(['<=', 'p.DateCreate', $dateTo->timestamp])
            ->groupBy('pp.PartnerId')
            ->all();

        foreach ($data as $row) {
            $result[] = $row['PartnerId'];
        }
        return $result;
    }

}
