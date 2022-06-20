<?php


namespace app\models\sms\api;


use app\models\payonline\Cards;
use app\models\payonline\Partner;
use app\models\sms\ISms;
use app\models\sms\Message;
use app\models\sms\PaymentOrders;
use app\models\sms\Stop;
use app\models\sms\tables\AccessSms;
use app\services\CurlLogger;
use qfsx\yii2\curl\Curl;
use app\models\extservice\HttpProxy;
use Yii;
use yii\db\ActiveRecord;
use yii\helpers\VarDumper;

/**
 * @property array     $phones
 * @property string    $message
 * @property object    $response;
 * @property AccessSms $access
 * */
class SingleMainSms implements ISms
{
    private $phones;
    private $message;
    private $response;
    private $access;

    /**
     * @param string $message - строка которая будет непосредственно в смс
     * @param array  $phones - номера телефоно (строки) ['89876782312', '8 777 666 66 66']
     * Не много о формате номера:
     * Номер должен соответствовать международному формату E.164.
     * Номер может быть с пробелами.
     * Номер может содержать максимум 15 цифр.
     * Формат номер поддерживает "+"
     */
    public function __construct(string $message, array $phones, ActiveRecord $access)
    {
        $this->phones = $this->phoneToCorrectFormat($phones);
        $this->message = $message;
        $this->access = $access;

    }

    /**
     * Вторичный конструктор.
     *
     * @param Message $msg
     *
     * @return SingleMainSms
     */
    public static function buidWithMessage(Message $msg): self
    {
        /**
         * @var Partner $partner
         * @var AccessSms $access
        */
        $partner = Yii::$app->user->identity->getPartner();
        $access = AccessSms::find()->where(['partner_id'=>$partner, 'description'=>'MainSms.ru'])->one();
        if (!$access or !$access->secret_key or !$access->public_key){
            Stop::app(400, 'У вас нет доступа к смс сервису, пожалуйста обратитесь к администратору, за подробной информацией.');
        }
        return new self($msg->content(), $msg->phones(), $access);
    }

    /**
     * Отправляет смс сообщения
     */
    public function send(): void
    {
        Yii::warning('MainSms send: ' . print_r($this->fields(), true), 'mfo');

        $curl = new Curl();
        $curl->setOptions($this->params());
        $curl->setOption(CURLOPT_VERBOSE, Yii::$app->params['VERBOSE'] === 'Y');
        try {
            $curl->get('https://mainsms.ru/api/mainsms/message/send?' . $this->fields());
            $this->response = $curl->response;

            (new CurlLogger($curl, 'https://mainsms.ru/api/mainsms/message/send?' . $this->fields(), [], [], Cards::MaskCardLog($curl->response)))();

            if ($curl->errorCode) {
                Yii::warning('MainSms error: ' . $curl->errorCode . ":" . $curl->errorText, 'mfo');
            }
        } catch (\Exception $e) {
            $this->response = '';
            Yii::warning('MainSms Exception:' . $e->getMessage(), 'mfo');
        }

        Yii::warning('MainSms ans: ' . $curl->response, 'mfo');
    }

    /**
     * Возвращает ответ от сервиса который предоставлет рассылку смс
     */
    public function response(): array
    {
        if ($this->response) {
            return json_decode($this->response, true);
        }
        return [];
    }

    /**
     * Говорит о том удачно ли прошла отправка смс (анализирует ответ от сервера).
     */
    public function successful(): bool
    {
        $resp = $this->response();
        if ($resp['status'] == 'success') {
            return true;
        }
        return false;
    }

    private function phoneToCorrectFormat(array $phones): array
    {
        $new_phones = [];
        foreach ($phones as $phone) {
            $new_phones[] = str_replace(' ', '', $phone);
        }
        return $new_phones;
    }

    private function projectName(): string
    {
//        return 'vepay_test';
//        return 'vepay';
        return $this->access->public_key;
    }

    private function api_token(): string
    {
//        return '9593a5ea76462';
//        return '1df5aa62b4868';
        return $this->access->secret_key;
    }

    private function params(): array
    {
        return [
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_CIPHER_LIST => 'TLSv1'
        ];
    }

    /**
     * @return string format url
     * */
    private function fieldsWithoutSign(): array
    {
        $fields = [
            'project' => $this->projectName(),
            'recipients' => $this->recipients(),
            'message' => $this->message,
            'sender' => 'Lemon',
        ];
        if (Yii::$app->params['DEVMODE'] == 'Y') {
            $fields['test'] = 1;
        }
        return $fields;

    }

    private function fields(): string
    {
        $fields = $this->fieldsWithoutSign();
        //формируем подпись
        $fields['sign'] = $this->signature($fields);
        return http_build_query($fields);
    }

    private function recipients(): string
    {
        return implode(',', $this->phones);
    }

    private function signature(array $fields): string
    {
        ksort($fields);
        $step1 = implode(';', $fields) . ';' . $this->api_token();
        $step2 = sha1($step1);
        $step3 = md5($step2);
        return $step3;
    }
}