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
