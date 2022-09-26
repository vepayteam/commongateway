<?php

namespace app\modules\partner\models\data;

use app\models\user\UserIdentification;
use yii\base\Model;
use yii\helpers\ArrayHelper;

class IdentificationListItem extends Model
{
    /**
     * @see UserIdentification::$ID
     * @var int
     */
    public $id;
    /**
     * @see UserIdentification::$DateCreate
     * @var int
     */
    public $createdAt;
    /**
     * @see UserIdentification::$TransNum
     * @var int
     */
    public $transactionNumber;
    /**
     * @see UserIdentification::$Name
     * @var string
     */
    public $firstName;
    /**
     * @see UserIdentification::$Fam
     * @var string
     */
    public $lastName;
    /**
     * @see UserIdentification::$Otch
     * @var string
     */
    public $middleName;
    /**
     * @see UserIdentification::$Inn
     * @var string
     */
    public $inn;
    /**
     * @see UserIdentification::$Snils
     * @var string
     */
    public $snils;
    /**
     * @see UserIdentification::$PaspSer
     * @var string
     */
    public $passportSeries;
    /**
     * @see UserIdentification::$PaspNum
     * @var string
     */
    public $passportNumber;
    /**
     * @see UserIdentification::$PaspPodr
     * @var string
     */
    public $passportDepartmentCode;
    /**
     * @see UserIdentification::$PaspDate
     * @var int
     */
    public $passportIssueDate;
    /**
     * @see UserIdentification::$PaspVidan
     * @var int
     */
    public $passportIssuedBy;

    /**
     * {@inheritDoc}
     */
    public function attributeLabels(): array
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'id' => 'ID',
            'createdAt' => 'Дата запроса',
            'transactionNumber' => 'Код транзакции',
            'firstName' => 'Имя',
            'lastName' => 'Фамилия',
            'middleName' => 'Отчество',
            'inn' => 'ИНН',
            'snils' => 'СНИЛС',
            'passportSeries' => 'Паспорт серия',
            'passportNumber' => 'Паспорт номер',
            'passportDepartmentCode' => 'Паспорт подразд.',
            'passportIssueDate' => 'Паспорт дата',
            'passportIssuedBy' => 'Папорт выдан',
        ]);
    }

    public function mapUserIdentification(UserIdentification $identification): IdentificationListItem
    {
        $this->id = $identification->ID;
        $this->createdAt = $identification->DateCreate;
        $this->transactionNumber = $identification->TransNum;
        $this->firstName = $identification->Name;
        $this->lastName = $identification->Fam;
        $this->middleName = $identification->Otch;
        $this->inn = $identification->Inn;
        $this->snils = $identification->Snils;
        $this->passportSeries = $identification->PaspSer;
        $this->passportNumber = $identification->PaspNum;
        $this->passportDepartmentCode = $identification->PaspPodr;
        $this->passportIssueDate = $identification->PaspDate;
        $this->passportIssuedBy = $identification->PaspVidan;

        return $this;
    }
}