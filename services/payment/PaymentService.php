<?php


namespace app\services\payment;


use app\models\bank\TCBank;
use app\models\bank\TcbGate;
use app\models\kfapi\KfPay;
use app\models\kfapi\KfRequest;
use app\models\partner\admin\VyvodParts;
use app\models\PayschetPart;
use app\services\payment\payment_strategies\CreateFormEcomStrategy;
use app\services\payment\payment_strategies\CreateFormJkhStrategy;
use app\services\payment\payment_strategies\IPaymentStrategy;
use Carbon\Carbon;
use Yii;
use yii\db\Query;

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
                $this->payPartsSenderToRecipient($senderPartnerId, $recipientPartnerId, $dateFrom, $dateTo);
            }
        }
    }

    private function payPartsSenderToRecipient($senderPartnerId, $recipientPartnerId, Carbon $dateFrom, Carbon $dateTo)
    {
        Yii::$app->db->beginTransaction();

        $vyvodParts = new VyvodParts();
        $vyvodParts->SenderId = $senderPartnerId;
        $vyvodParts->RecipientId = $recipientPartnerId;
        $vyvodParts->PayschetId = 0;
        $vyvodParts->Amount = 0;
        $vyvodParts->DateCreate = Carbon::now()->timestamp;
        $vyvodParts->save();

        $data = PayschetPart::find()
            ->innerJoin('pay_schet', ['pay_schet.Id' => 'pay_schet_parts.PayschetId'])
            ->where([
                'p.Status' => 1,
                'pay_schet_parts.PartnerId' => $recipientPartnerId,
                'pay_schet.IdOrg' => $senderPartnerId,

            ])
            ->andWhere(['=', 'pay_schet.IdOrg', $senderPartnerId])
            ->andWhere(['>=', 'pay_schet.DateCreate', $dateFrom->timestamp])
            ->andWhere(['<=', 'pay_schet.DateCreate', $dateTo->timestamp])
            ->all();

        /** @var PayschetPart $row */
        foreach ($data as $payschetPart) {

        }
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
            ->from('pay_schet_parts as pp')
            ->innerJoin('pay_schet as p', ['p.ID' => 'pp.PayschetId'])
            ->where([
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

    private function getPartsRecipientPartnerIds($partnerId, Carbon $dateFrom, Carbon $dateTo)
    {
        $result = [];
        $query = new Query();
        $data = $query
            ->select([
                'pp.PartnerId',
            ])
            ->from('pay_schet_parts as pp')
            ->innerJoin('pay_schet as p', ['p.ID' => 'pp.PayschetId'])
            ->where([
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
