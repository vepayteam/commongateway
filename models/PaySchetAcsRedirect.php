<?php

namespace app\models;

use app\services\payment\models\PaySchet;
use yii\db\ActiveRecord;
use yii\helpers\Json;

/**
 * Data for redirection to ACS (Access Control Server used in 3DS verification) page.
 *
 * @property int $id ID equals to {@see PaySchet::$ID}.
 * @property int $status Status.
 * @property string $url URL of ACS verification page.
 * @property string $method HTTP method ("GET" or "POST") to open the verification page. {@see PaySchetAcsRedirect::methodList()}
 * @property string $postParametersJson JSON-encoded POST-parameters.
 * @property int $createdAt
 * @property int $updatedAt
 */
class PaySchetAcsRedirect extends ActiveRecord
{
    const STATUS_OK = 1;
    const STATUS_PENDING = 2;
    const METHOD_GET = 'GET';
    const METHOD_POST = 'POST';

    /**
     * @var array
     */
    public $postParameters;

    /**
     * {@inheritDoc}
     */
    public static function tableName(): string
    {
        return 'pay_schet_acs_redirect';
    }

    /**
     * @return string[]
     */
    public static function methodList(): array
    {
        return [static::METHOD_GET, static::METHOD_POST];
    }

    /**
     * {@inheritDoc}
     */
    public function afterFind()
    {
        if ($this->postParametersJson !== null) {
            $this->postParameters = Json::decode($this->postParametersJson);
        }

        parent::afterFind();
    }

    /**
     * {@inheritDoc}
     */
    public function beforeSave($insert): bool
    {
        if ($this->method !== null) {
            $this->method = mb_strtoupper($this->method);
        }

        if ($this->postParameters === null) {
            $this->postParametersJson = null;
        } else {
            $this->postParametersJson = Json::encode($this->postParameters);
        }

        if ($insert) {
            $this->createdAt = time();
        }
        $this->updatedAt = time();

        return parent::beforeSave($insert);
    }
}