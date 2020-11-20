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
        $certPath = Yii::getAlias('@app/config/runacert/');
        $url = sprintf(
            '%s/%s/%s/%s',
            Yii::$app->params['services']['ident']['runaDomain'],
            $mode,
            Yii::$app->params['services']['ident']['runaLogin'],
            $method
        );

        $data = $model->getAttributes();

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            // CURLOPT_SSLVERSION => 6,
            CURLOPT_SSLCERT => $certPath . '/vepay.crt',
            CURLOPT_SSLKEY => $certPath . '/vepay.key',

            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/json"
            ),
        ));

        try {
            $response = curl_exec($curl);
            curl_close($curl);
            return json_decode($response, true);
        } catch (\Exception $e) {
            throw new RunaIdentException('Ошибка запроса');
        }
    }
}
