<?php

namespace app\models\crypt;

use Yii;

/**
 * This is the model class for table "key_users".
 *
 * @property int $ID
 * @property string $Login login
 * @property string $Password pw sha2
 * @property string|null $FIO fio
 * @property string|null $Email email
 * @property int $Key1Admin admin vvoda klucha1
 * @property int $Key2Admin admin vvoda klucha2
 * @property int $Key3Admin admin vvoda klucha3
 * @property int $KeyChageAdmin admin zameny kychei
 * @property int DateChange data izmemenia
 * @property int AutoLockDate data avtoblokirovki
 * @property int $IsActive 0 - off 1 - on
 * @property int $IsDeleted 1 - udaleno
 */
class KeyUsers extends \yii\db\ActiveRecord
{
    public static $logType = [
        1 => 'вход',
        2 => 'ошибка входа',
        3 => 'смена пароля',
        4 => 'внесение ключа 1',
        5 => 'внесение ключа 2',
        6 => 'внесение ключа 3',
        7 => 'смена ключей',
        8 => 'ошибка смены пароля',
        9 => 'выход',
        10 => 'блокировка пользователя',
        11 => 'разблокировка пользователя',
        12 => 'создание пользователя',
        13 => 'удаление пользователя',
    ];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'key_users';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['Login', 'Password'], 'required'],
            [['Key1Admin', 'Key2Admin', 'Key3Admin', 'KeyChageAdmin', 'IsActive', 'IsDeleted'], 'integer'],
            [['Login'], 'string', 'max' => 20],
            [['Password', 'FIO'], 'string', 'max' => 100],
            [['Email'], 'string', 'max' => 50],
            [['Login'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'ID' => 'ID',
            'Login' => 'Логин',
            'Password' => 'Пароль',
            'FIO' => 'ФИО',
            'Email' => 'Электронная почта',
            'Key1Admin' => 'Key1 Admin',
            'Key2Admin' => 'Key2 Admin',
            'Key3Admin' => 'Key3 Admin',
            'KeyChageAdmin' => 'Key Chage Admin',
            'DateChange' => 'Дата смены пароля',
            'IsActive' => 'Is Active',
            'IsDeleted' => 'Is Deleted',
        ];
    }
}
