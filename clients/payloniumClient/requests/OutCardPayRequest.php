<?php

namespace app\clients\payloniumClient\requests;

class OutCardPayRequest extends BaseRequest
{
    /**
     * @var int
     */
    private $paymentId;

    /**
     * @var int
     */
    private $sum;

    /**
     * @var int
     */
    private $service;

    /**
     * @var string
     */
    private $account;

    /**
     * @var string
     */
    private $date;

    /**
     * @var string|null
     */
    private $phone;

    /**
     * @param int $paymentId
     * @param int $sum
     * @param int $service
     * @param string $account
     * @param string $date Дата создания платежа в формате стандарта ISO 8601
     * @param string|null $phone
     */
    public function __construct(int $paymentId, int $sum, int $service, string $account, string $date, ?string $phone)
    {
        parent::__construct();

        $this->paymentId = $paymentId;
        $this->sum = $sum;
        $this->service = $service;
        $this->account = $account;
        $this->date = $date;
        $this->phone = $phone;
    }

    /**
     * @inheritdoc
     */
    public function toRequestString(): string
    {
        $xmlRequest = $this->getCommonRequestXmlElement();
        $xmlRequest->addChild('payment');
        $xmlRequest->payment->addAttribute('id', $this->paymentId);
        $xmlRequest->payment->addAttribute('sum', $this->sum);
        $xmlRequest->payment->addAttribute('service', $this->service);
        $xmlRequest->payment->addAttribute('account', $this->account);
        $xmlRequest->payment->addAttribute('date', $this->date);

        if ($this->phone) {
            $xmlRequest->payment->addChild('attribute');
            $xmlRequest->payment->attribute->addAttribute('name', 'phone');
            $xmlRequest->payment->attribute->addAttribute('value', $this->phone);
        }

        return $this->convertXmlElementToString($xmlRequest);
    }
}