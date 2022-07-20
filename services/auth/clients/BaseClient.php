<?php


namespace app\services\auth\clients;


use app\models\payonline\Cards;
use app\services\auth\AuthService;
use app\services\auth\models\IClientForm;
use app\services\auth\models\User;
use app\services\DeprecatedCurlLogger;
use Requests;
use Yii;
use yii\base\Model;

abstract class BaseClient
{
    protected $url;
    /** @var null|string  */
    protected $uri = null;

    public function __construct()
    {
        $this->url = Yii::$app->params['services']['accounts']['url'];
    }

    abstract function call(IClientForm $model);

    /**
     * @param array $data
     * @return bool|string
     */
    protected function sendRequest(array $data)
    {
        $headers = $this->getRequestHeaders();

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_VERBOSE => Yii::$app->params['VERBOSE'] === 'Y',
            CURLOPT_URL => $this->url.$this->uri,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => $headers,
        ));

        $response = curl_exec($curl);
        curl_close($curl);

        DeprecatedCurlLogger::handle(curl_getinfo($curl), $this->url.$this->uri, $headers, Cards::MaskCardLog($data), Cards::MaskCardLog($response));

        return json_decode($response, true);
    }

    private function getRequestHeaders()
    {
        $result = [
            'Accept' => 'application/json',
        ];

        if(!Yii::$app->user->isGuest) {
            /** @var User $user */
            $user = Yii::$app->user->identity;
            $result['Authorization'] = $user->getActualToken()->Token;
        }
        return $result;
    }

    /**
     * @return AuthService
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\di\NotInstantiableException
     */
    protected function getAuthService()
    {
        return Yii::$container->get('AuthService');
    }

}
