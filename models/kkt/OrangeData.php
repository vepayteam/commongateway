<?php

namespace app\models\kkt;

use app\models\extservice\HttpProxy;
use Yii;
use yii\base\InvalidArgumentException;
use yii\helpers\Json;

class OrangeData implements IKkm
{
    use HttpProxy;

    public $keySign;
    public $keyFile;
    public $keyPw;
    public $certFile;
    public $ca_cert;
    public $inn;

    private $url;
    private $OrangedataClient;

    public function __construct($config)
    {
        $this->inn = $config['inn'];
        $this->keySign = Yii::$app->basePath . '/config/kassaclients/'.$config['keySign'];
        $this->keyFile = Yii::$app->basePath . '/config/kassaclients/'.$config['keyFile'];
        $this->certFile = Yii::$app->basePath . '/config/kassaclients/'.$config['certFile'];

        /*
        $this->inn = '7728487400';
        $this->keySign = Yii::$app->basePath . '/config/kassa/7728487400_sign.key';
        $this->keyFile = Yii::$app->basePath . '/config/kassa/7728487400.key';
        $this->certFile = Yii::$app->basePath . '/config/kassa/7728487400.crt';
        */
        $this->keyPw = '';

        $this->url = 'https://api.orangedata.ru:12003';
        $this->ca_cert = Yii::$app->basePath . '/config/kassa/cacert.pem';

        if (Yii::$app->params['DEVMODE'] == 'Y' || Yii::$app->params['TESTMODE'] != 'Y') {
            $this->url = 'https://apip.orangedata.ru:2443';

            $this->inn = '7728487400';
            $this->keySign = Yii::$app->basePath . '/config/kassa/private_key_test.pem';
            $this->keyFile = Yii::$app->basePath . '/config/kassa/client.key';
            $this->certFile = Yii::$app->basePath . '/config/kassa/client.crt';
            $this->keyPw = '1234';
        }

        $this->OrangedataClient = new OrangedataClient([
            'inn' => $this->inn,
            'api_url' => $this->url,
            'sign_pkey' => $this->keySign,
            'ssl_client_key' => $this->keyFile,
            'ssl_client_crt' => $this->certFile,
            'ssl_ca_cert' => $this->ca_cert,
            'ssl_client_crt_pass' => $this->keyPw
        ]);
    }

    /**
     * Создание чека
     * @param int $id
     * @param DraftData $data
     * @return array
     */
    public function CreateDraft($id, DraftData $data)
    {
        $this->OrangedataClient
            ->create_order([
                'id' => $id,
                'type' => 1,
                'customerContact' => $data->customerContact,
                'taxationSystem' => 2,
                'group' => 'main',
                'key' => $this->inn
            ])
            ->add_position_to_order([
                'quantity' => $data->quantity,
                'price' => $data->price,
                'tax' => $data->tax,
                'text' => $data->text,
                'paymentMethodType' => 4,
                'paymentSubjectType' => 10,
                'nomenclatureCode' => null,
                'supplierInfo' => null,
                'supplierINN' => null,
                'agentType' => null,
                'agentInfo' => null,
                'unitOfMeasurement' => null,
                'additionalAttribute' => null,
                'manufacturerCountryCode' => null,
                'customsDeclarationNumber' => null,
                'excise' => 0
            ])
            ->add_payment_to_order(['type' => 2, 'amount' => $data->price]);

        try {
            $result = $this->OrangedataClient->send_order();
            if ($result === true) {
                return ['status' => 1];
            }
            Yii::warning($result, 'rsbcron');
        } catch (\Exception $e) {
            Yii::warning($e->getMessage(), 'rsbcron');
        }
        return ['status' => 0];
    }

    /**
     * Данные чека
     * @param $id
     * @return array
     */
    public function StatusDraft($id)
    {
        try {
            $i = 0;
            do {
                $i++;
                $result = $this->OrangedataClient->get_order_status($id);
                if ($result === true) {
                    sleep(1);
                }
            } while ($result === true && $i < 30);

            //print_r($result);die("!");
            try {
                if ($result === false) {
                    return ['status' => 0];
                }
                $ret = Json::decode($result);
                if (!isset($ret['errors'])) {
                    return ['status' => 1, 'data' => $ret];
                } else {
                    Yii::warning($ret['errors'], 'rsbcron');
                }
            } catch (InvalidArgumentException $e) {
                Yii::warning($e->getMessage(), 'rsbcron');
            }
        } catch (\Exception $e) {
            Yii::warning($e->getMessage(), 'rsbcron');
        }
        return ['status' => 0];

    }
}