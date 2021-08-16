<?php

namespace app\modules\h2hapi\v1\objects;

use app\components\api\ApiObject;
use app\services\payment\models\PaySchet;

/**
 * REST-объект Клиент в Счете.
 */
class InvoiceClientObject extends ApiObject
{
    /**
     * @var string ФИО клиента.
     */
    public $fullName;
    /**
     * @var string
     */
    public $address;
    /**
     * @var string
     */
    public $email;
    /**
     * @var string
     */
    public $login;
    /**
     * @var string
     */
    public $phone;
    /**
     * @var string
     */
    public $zip;

    /**
     * {@inheritDoc}
     */
    public function rules(): array
    {
        return [
            [['phone', 'login'], 'string', 'max' => 32],
            [['fullName'], 'string', 'max' => 80],
            [['email'], 'email'],
            [['address'], 'string', 'max' => 255],
            [['zip'], 'string', 'max' => 16],
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function fields(): array
    {
        return [
            'fullName',
            'email',
            'address',
            'login',
            'phone',
            'zip',
        ];
    }

    /**
     * @param PaySchet $paySchet
     * @return InvoiceClientObject
     */
    public function mapPaySchet(PaySchet $paySchet): InvoiceClientObject
    {
        $this->fullName = $paySchet->FIO;
        $this->email = $paySchet->UserEmail;
        $this->address = $paySchet->AddressUser;
        $this->phone = $paySchet->PhoneUser;
        $this->login = $paySchet->LoginUser;
        $this->zip = $paySchet->ZipUser;

        return $this;
    }
}