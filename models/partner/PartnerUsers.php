<?php

namespace app\models\partner;

use Yii;

/**
 * This is the model class for table "partner_users".
 *
 * @property int $ID [int(10) unsigned]
 * @property string $Login [varchar(20)]  login
 * @property string $Password [varchar(100)]  pw sha2
 * @property bool $IsAdmin [tinyint(1) unsigned]  1 - admin 0 - partner
 * @property int $IdPartner [int(10) unsigned]  id partner
 * @property string $FIO [varchar(100)]  fio
 * @property string $Email [varchar(50)]  email
 * @property string $Doljnost [varchar(100)]  doljnost
 * @property bool $IsActive [tinyint(1) unsigned]  0 - off 1 - on
 * @property bool $IsDeleted [tinyint(1) unsigned]  1 - udaleno
 * @property bool $RoleUser [tinyint(1) unsigned]  0 - user 1 - partner admin
 * @property int $DateLastLogin [int(10) unsigned]  дата последнего входа в кабинет
 * @property int $ErrorLoginCnt [int(10) unsigned]  Число неудачных попыток входа
 * @property int $DateErrorLogin [int(10) unsigned]  Дата последней неудачной попытки входа
 * @property int $AutoLockDate [int(10) unsigned]  блокировка с этой даты
 */
class PartnerUsers extends \yii\db\ActiveRecord
{
    public $Password2;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'partner_users';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['IsAdmin', 'IdPartner', 'RoleUser', 'IsActive'], 'integer'],
            [['Login', 'Password', 'Password2'], 'string', 'max' => 20],
            [['Email'], 'string', 'max' => 50],
            [['FIO', 'Doljnost'], 'string', 'max' => 100],
            [['Login'], 'required'],
            [['Login'], 'unique'],
            [['RoleUser', 'IsActive'], 'ruleRole'],
            [['Password'], 'rulePasswordCheck'],
        ];
    }

    public function ruleRole($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $adminExist = PartnerUsers::find()
                ->where([
                    'IdPartner' => $this->IdPartner,
                    'IsDeleted' => 0,
                    'RoleUser' => 1
                ])
                ->andWhere(['<>', 'ID', $this->ID])
                ->count();
            if ($this->RoleUser == 0 && !$adminExist) {
                $this->addError($attribute, 'Нет других администраторов, необходимо предоставить права администратора');
            }
        }
    }

    public function rulePasswordCheck($attribute, $params)
    {
        if (!$this->hasErrors()) {
            if ($this->isNewRecord && empty($this->Password)) {
                $this->addError($attribute, 'Задайте пароль');
            } elseif (!empty($this->Password) && $this->Password != $this->Password2) {
                $this->addError($attribute, 'Пароли не совпадают');
            } elseif (strlen($this->Password) < 8 || !preg_match('/(?=.*[0-9])(?=.*[a-zA-Z])/ius', $this->Password)) {
                $this->addError($attribute, 'Пароль слишком простой');
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'ID' => 'ID',
            'Login' => 'Логин',
            'Password' => 'Пароль',
            'Password2' => 'Повтор пароля',
            'RoleUser' => 'Администратор',
            'FIO' => 'ФИО',
            'Doljnost' => 'Должность',
            'Email' => 'E-mail',
            'IsActive' => 'Активен',
            'IsDeleted' => '1 - udaleno',
        ];
    }

    public static function getList($IdPart)
    {
        $partusers = PartnerUsers::find()->where(['IsDeleted' => 0]);
        if ($IdPart > 0) {
            $partusers->andWhere(['IdPartner' => $IdPart]);
        }
        return $partusers->all();
    }

    public function beforeSave($insert)
    {
        if ($insert || (!empty( $this->Password) && $this->Password <> $this->oldAttributes['Password'])) {
            $this->Password = hash('sha256', $this->Password);
        } elseif (empty($this->Password)) {
            $this->Password = $this->oldAttributes['Password'];
        }
        return parent::beforeSave($insert);
    }

    public function getPartUserAccess()
    {
        return $this->hasMany(PartUserAccess::className(), ['IdUser' => 'ID']);
    }
}
