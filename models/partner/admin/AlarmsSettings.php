<?php

namespace app\models\partner\admin;

/**
 * This is the model class for table "alarms_settings".
 *
 * @property int $ID
 * @property int $TypeAlarm tip opoveshenia
 * @property int $TimeAlarm nastroika minut
 * @property string $EmailAlarm email dlia opoveschenia
 */
class AlarmsSettings extends \yii\db\ActiveRecord
{
    /** Bank doesn't change status or respond. */
    public const TYPE_BANK_NO_RESPONSE = 0;
    /** SMS gate doesn't respond. */
    public const TYPE_SMS_GATE_NO_RESPONSE = 1;
    /** Status doesn't change. */
    public const TYPE_STATUS_FREEZE = 2;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'alarms_settings';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['TypeAlarm', 'TimeAlarm'], 'integer'],
            [['EmailAlarm'], 'string', 'max' => 255],
            [['TypeAlarm'], 'unique']
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'ID' => 'ID',
            'TypeAlarm' => 'Тип оповещения',
            'TimeAlarm' => 'Минуты',
            'EmailAlarm' => 'E-mail'
        ];
    }


    public function GetError()
    {
        $err = $this->firstErrors;
        $err = array_pop($err);
        return $err;
    }

    /**
     * @param $post
     * @param $error
     * @return int
     */
    public static function SaveAll($post, &$error)
    {
        if (isset($post['Settings']) &&
            isset($post['SettingsEmail']) &&
            preg_match('/^(([-a-zA-Z0-9._]+@[-a-zA-Z0-9.]+(\.[-a-zA-Z0-9]+)+),?)*$/ius', $post['SettingsEmail'])
        ) {
            foreach ($post['Settings'] as $tp => $mnt) {
                $setting = self::findOne(['TypeAlarm' => $tp]);
                if (!$setting) {
                    $setting = new self();
                    $setting->TypeAlarm = $tp;
                }
                if ($mnt < 10) {
                    $error = "Таймаут меньше допустимого";
                    return 0;
                }
                $setting->TimeAlarm = (int)$mnt;
                $setting->EmailAlarm = $post['SettingsEmail'];
                if (!$setting->save()) {
                    $error = $setting->GetError();
                    return 0;
                }
            }
            return 1;
        }
        $error = "Неверный адрес почты";
        return 0;
    }

}
