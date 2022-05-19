<?php

namespace app\services\yandex\models;

use app\models\payonline\Partner;
use yii\base\Model;

class EncryptionKeyReader extends Model
{
    private const BASE_KEY_PATH = '@app/config/yandexPay/';

    /**
     * @var Partner
     */
    public $partner;

    /**
     * @inheritdoc
     */
    public function __construct(array $config)
    {
        parent::__construct($config);
    }

    public function readPublicAuthKey()
    {
        $path = \Yii::getAlias(self::BASE_KEY_PATH . $this->partner->yandexPayAuthPublic);

        return file_get_contents($path);
    }

    public function readPrivateAuthKey()
    {
        $path = \Yii::getAlias(self::BASE_KEY_PATH . $this->partner->yandexPayAuthPrivate);

        return file_get_contents($path);
    }

    public function readPrivateEncryptionKey()
    {
        $path = \Yii::getAlias(self::BASE_KEY_PATH . $this->partner->yandexPayEncryptionPrivate);

        return file_get_contents($path);
    }

    public function readPublicEncryptionKey()
    {
        $path = \Yii::getAlias(self::BASE_KEY_PATH . $this->partner->yandexPayEncryptionPublic);

        return file_get_contents($path);
    }
}