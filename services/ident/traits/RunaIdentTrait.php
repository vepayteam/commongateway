<?php


namespace app\services\ident\traits;


use app\services\ident\exceptions\RunaIdentException;
use app\services\ident\forms\RunaIdentInitForm;
use app\services\ident\forms\RunaIdentStateForm;
use app\services\ident\forms\UploadCertForm;
use app\services\ident\models\IdentRuna;
use app\services\ident\responses\RunaIdentInitResponse;
use app\services\ident\responses\RunaIdentStateResponse;
use app\services\ident\responses\UploadCertRunaResponse;
use Yii;
use yii\base\Model;

trait RunaIdentTrait
{
    public function runaInit(RunaIdentInitForm $runaIdentInitForm)
    {
        try {
            $response = $this->sendRunaRequest('init', $runaIdentInitForm, 'verify_docs');
            if($response['state_code'] != '00000') {
                throw new RunaIdentException($response['state_description']);
            }
        } catch (RunaIdentException $e) {
            throw $e;
        }
        $runaIdentInitResponse = new RunaIdentInitResponse();
        $runaIdentInitResponse->load($response, '');

        $identRuna = new IdentRuna();
        $identRuna->PartnerId = Yii::$app->session->get('partnerId');
        $identRuna->Tid = $runaIdentInitResponse->tid;
        $identRuna->DateCreate = time();

        if($identRuna->save()) {
            $runaIdentInitResponse->identRuna = $identRuna;
            return $runaIdentInitResponse;
        } else {
            throw new \Exception('Ошибка при сохранении');
        }
    }

    /**
     * @param RunaIdentStateForm $runaIdentStateForm
     * @return RunaIdentStateResponse
     * @throws RunaIdentException
     */
    public function runaState(RunaIdentStateForm $runaIdentStateForm)
    {
        try {
            $response = $this->sendRunaRequest('state', $runaIdentStateForm, 'verify_docs');
        } catch (RunaIdentException $e) {
            throw $e;
        }
        $runaIdentStateResponse = new RunaIdentStateResponse();
        $runaIdentStateResponse->load($response, '');

        return $runaIdentStateResponse;
    }

    /**
     * @param UploadCertForm $uploadCertForm
     * @return UploadCertRunaResponse
     * @throws RunaIdentException
     */
    public function sendCertRuna(UploadCertForm $uploadCertForm)
    {
        $method = 'upload_certificate';
        $mode = 'public';

        $response = $this->sendRunaRequest($method, $uploadCertForm, $mode);
        $uploadCertRunaResponse = new UploadCertRunaResponse();
        $uploadCertRunaResponse->load($response, '');

        return $uploadCertRunaResponse;
    }

    /**
     * @param $method
     * @param Model $model
     * @param $mode
     * @return mixed
     * @throws RunaIdentException
     */
    protected function sendRunaRequest($method, Model $model, $mode)
    {
        $certPath = Yii::getAlias('@app/config/runacert');
        $url = sprintf(
            '%s/%s/%s/%s',
            Yii::$app->params['services']['ident']['runaDomain'],
            $mode,
            Yii::$app->params['services']['ident']['runaLogin'],
            $method
        );

        $data = $model->getAttributes();

        $curl = curl_init($url);

        curl_setopt_array($curl, array(
            CURLOPT_SSLVERSION => CURL_SSLVERSION_TLSv1_2,
            CURLOPT_SSLCERT => $certPath . '/vepay.crt',
            CURLOPT_SSLKEY => $certPath . '/vepay.key',
            CURLOPT_CAINFO => $certPath . '/runa.crt',
            CURLOPT_CERTINFO => true,
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_SSL_VERIFYPEER => true,

            CURLOPT_HTTPHEADER => array(
                'Accept: application/json',
                'Content-Type: application/json'
            ),
            CURLOPT_POSTFIELDS => json_encode($data)
        ));

        try {
            $response = curl_exec($curl);
            $error = curl_error($curl);

            if(!empty($error)) {
                throw new RunaIdentException($error);
            }
            curl_close($curl);
            return json_decode($response, true);
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
