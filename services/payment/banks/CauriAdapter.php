<?php


namespace app\services\payment\banks;


use app\services\payment\banks\bank_adapter_responses\BaseResponse;
use app\services\payment\banks\bank_adapter_responses\OutCardPayResponse;
use app\services\payment\exceptions\GateException;
use app\services\payment\forms\AutoPayForm;
use app\services\payment\forms\cauri\OutCardPayRequest;
use app\services\payment\forms\CreatePayForm;
use app\services\payment\forms\DonePayForm;
use app\services\payment\forms\OkPayForm;
use app\services\payment\forms\OutCardPayForm;
use app\services\payment\forms\RefundPayForm;
use app\services\payment\models\PartnerBankGate;
use Vepay\Cauri\Client\Request\PayoutCreateRequest;
use Vepay\Cauri\Resource\Payout;
use Vepay\Gateway\Config;
use Vepay\Gateway\Logger\Logger;

class CauriAdapter implements IBankAdapter
{
    const IS_CONFIG_OUT_CARD_PARAMS_CACHE_PREFIX = 'Cauri_IsConfigOutCardParams';

    public static $bank = 8;

    /** @var PartnerBankGate */
    protected $gate;


    /**
     * @inheritDoc
     */
    public function setGate(PartnerBankGate $partnerBankGate)
    {
        $this->gate = $partnerBankGate;

        $config = Config::getInstance();
        $config->logger = Logger::class;
    }

    /**
     * @inheritDoc
     */
    public function getBankId()
    {
        return self::$bank;
    }

    /**
     * @inheritDoc
     */
    public function confirm(DonePayForm $donePayForm)
    {
        throw new GateException('Метод недоступен');
    }

    /**
     * @inheritDoc
     */
    public function confirmPay($idpay, $org = 0, $isCron = false)
    {
        // TODO: Implement confirmPay() method.
    }

    /**
     * @inheritDoc
     */
    public function transferToCard(array $data)
    {
        // TODO: Implement transferToCard() method.
    }

    /**
     * @inheritDoc
     */
    public function createPay(CreatePayForm $createPayForm)
    {
        throw new GateException('Метод недоступен');
    }


    /**
     * @inheritDoc
     */
    public function PayXml(array $params)
    {
        // TODO: Implement PayXml() method.
    }

    /**
     * @inheritDoc
     */
    public function PayApple(array $params)
    {
        // TODO: Implement PayApple() method.
    }

    /**
     * @inheritDoc
     */
    public function PayGoogle(array $params)
    {
        // TODO: Implement PayGoogle() method.
    }

    /**
     * @inheritDoc
     */
    public function PaySamsung(array $params)
    {
        // TODO: Implement PaySamsung() method.
    }

    /**
     * @inheritDoc
     */
    public function ConfirmXml(array $params)
    {
        // TODO: Implement ConfirmXml() method.
    }

    /**
     * @inheritDoc
     */
    public function reversOrder($IdPay)
    {
        // TODO: Implement reversOrder() method.
    }

    /**
     * @inheritDoc
     */
    public function checkStatusPay(OkPayForm $okPayForm)
    {
        throw new GateException('Метод недоступен');
    }

    /**
     * @inheritDoc
     */
    public function recurrentPay(AutoPayForm $autoPayForm)
    {
        throw new GateException('Метод недоступен');
    }

    /**
     * @inheritDoc
     */
    public function refundPay(RefundPayForm $refundPayForm)
    {
        throw new GateException('Метод недоступен');
    }

    /**
     * @inheritDoc
     */
    public function outCardPay(OutCardPayForm $outCardPayForm)
    {
        $outCardPayRequest = new OutCardPayRequest();
        $outCardPayRequest->amount = $outCardPayForm->amount / 100;
        $outCardPayRequest->description = 'Выдача на карту №' . $outCardPayForm->paySchet->ID;
        $outCardPayRequest->orderId = $outCardPayForm->paySchet->ID;
        $outCardPayRequest->account = $outCardPayForm->cardnum;
        $outCardPayRequest->beneficiaryFirstName = $outCardPayForm->getFirstName();
        $outCardPayRequest->beneficiaryLastName = $outCardPayForm->getLastName();

        $payout = new Payout();
        $response = $payout->__call('create', [
            $outCardPayRequest->getAttributes(), [
                'public_key' => $this->gate->Login,
                'private_key' => $this->gate->Token,
            ]
        ]);

        $content = $response->getContent();
        $outCardPayResponse = new OutCardPayResponse();

        if(array_key_exists('id', $content) && !empty($content['id'])) {
            $outCardPayResponse->status = BaseResponse::STATUS_DONE;
            $outCardPayResponse->trans = $content['id'];
        } else {
            $outCardPayResponse->status = BaseResponse::STATUS_ERROR;
            $outCardPayResponse->message = 'Ошибка запроса';
        }

        return $outCardPayResponse;
    }
}
