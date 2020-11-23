<?php


namespace app\commands;


use app\services\ident\forms\UploadCertForm;
use app\services\ident\IdentService;
use Yii;
use yii\console\Controller;

class SecurityController extends Controller
{

    public function actionRunaGenerateCert($password)
    {
        $certPath = Yii::getAlias('@app/config/runacert');

        try {
            // unlink($certPath . '/vepay.key');
            // unlink($certPath . '/vepay.crt');
        } catch (\Exception $e) {}

        $cmd = sprintf('openssl req -new -newkey rsa:2048 -x509 -days 3650 -subj "/CN=api/O=vepay.online/1.2.643.3.131.1.1=7717794102/1.2.643.100.1=5147746099375/1.2.643.100.111=openssl" -keyout "%s/vepay.key" -out "%s/vepay.crt" -nodes',
            $certPath,
            $certPath
        );
        exec($cmd);

        $uploadCertForm = new UploadCertForm();
        $uploadCertForm->certificate = $this->transformCert(file_get_contents($certPath . '/vepay.crt'));
        $uploadCertForm->password = $password;

        $runaIdentStateResponse = $this->getIdentService()->sendCertRuna($uploadCertForm);

        if($runaIdentStateResponse->isSuccess()) {
            echo 'Успех';
        } else {
            echo 'Ошибка запроса';
        }
    }

    protected function transformCert($certStr)
    {
        $certStr = str_replace(['-----BEGIN CERTIFICATE-----', '-----END CERTIFICATE-----', "\r\n", "\n"], '', $certStr);
        return $certStr;
    }

    /**
     * @return IdentService
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\di\NotInstantiableException
     */
    protected function getIdentService()
    {
        return Yii::$container->get('IdentService');
    }

}
