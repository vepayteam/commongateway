<?php


namespace app\services\payment\forms\rsb_aft;


use Yii;
use yii\base\Model;

class OutCardPayCheckRequest extends Model implements XmlRequest
{
    public $target = 'moneytransfer';
    public $operation = 'check';
    public $transfer_type = 'cache2card';
    public $channel = 'term';

    public $card;
    public $tr_date;
    public $amount;
    public $fee = 0;
    public $fee2 = 0;


    /**
     * @inheritDoc
     */
    public function buildXml(string $privateKeyPath)
    {
        // TODO: Implement buildXml() method.
    }

    private function buildXmlRequest()
    {
        $request =  sprintf('<rsb_ns:target>%s</rsb_ns:target>
                <rsb_ns:operation>%s</rsb_ns:operation>
                <rsb_ns:transfer_type>%s</rsb_ns:transfer_type>
                <rsb_ns:channel>%s</rsb_ns:channel>
                <rsb_ns:tr_date>%s</rsb_ns:tr_date>
                <rsb_ns:card>%s</rsb_ns:card>
                <rsb_ns:amount>%d</rsb_ns:amount>
                <rsb_ns:fee>%d</rsb_ns:fee>
                <rsb_ns:fee2>%d</rsb_ns:fee2>', 
            $this->target, 
            $this->operation, 
            $this->transfer_type, 
            $this->channel, 
            $this->tr_date, 
            $this->card, 
            $this->amount, 
            $this->fee, 
            $this->fee2
        );

        return iconv('UTF-8', 'WINDOWS-1251', $request);
    }

    private function buildSignature($privateKeyPath)
    {
        $request = $this->buildXmlRequest();

        $requestHash = sha1($request);
        $privateKey = file_get_contents($privateKeyPath);

        $signature = null;
        openssl_sign($requestHash, $signature, $privateKey);
        return $signature;
    }
}
