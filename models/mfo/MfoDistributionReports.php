<?php


namespace app\models\mfo;

use Yii;
use yii\base\Model;
use yii\helpers\VarDumper;
use yii\validators\EmailValidator;

/**
 * @property array $email - принимает на вход массив email-ов
 * "email"=>[[IdPart => 'email'], ...]
 * @property array $repayment - принимает на вход массив чекбоксов "Выдача"
 * "repayment"=>[[IdPart => 0], ...]
 * @property array $payment - принимает на вход массив чекбоксов "Погашение"
 * "repayment"=>[[IdPart => 1], ...]
 */
class MfoDistributionReports extends Model
{

    public $method = 'post';
    public $formName = '';
    public $email;
    public $payment;
    public $repayment;

    public function init()
    {
        if ($this->method == 'post') {
            $this->load(Yii::$app->request->post(), $this->formName);
        } else {
            $this->load(Yii::$app->request->get(), $this->formName);
        }
    }

    public function rules()
    {
        return [
            ['email', 'required'],
            ['email', 'validateEmail'],
            [['payment', 'repayment'], 'each', 'rule' => ['number']]
        ];
    }

    public function validateEmail()
    {
        foreach ($this->email as $item => $email) {
            if (is_int($item) and $email !== '') {
                $validator = new EmailValidator();
                if (!$validator->validate($email)) {
                    //ошибка валидации появляется только когда неверно указан какой либо email
                    $this->addError('email', [
                            'error' => "Значение «" . $email . "» не является правильным email адресом",
                            'id' => $item
                        ]
                    );
                }
            }
        }
        if (!$this->hasErrors()) {
            return true;
        }
        return false;
    }

    /**
     * После валидации - удалить пустые поля с email
     * и все чекбоксы которые относятся к пустым email
     */
    public function afterValidate()
    {
        foreach ($this->email as $item => $email) {
            if ($email === '') {
                unset($this->email[$item]);
                unset($this->payment[$item]);
                unset($this->repayment[$item]);
            }
        }
        parent::afterValidate(); // TODO: Change the autogenerated stub
    }

    public function save()
    {
        foreach ($this->email as $partnerId => $email) {
            $record = DistributionReports::find()->where(['partner_id' => $partnerId])->one();
            if (!$record) {
                $record = new DistributionReports();
                $record->last_send = 0;
            }
            /**@var DistributionReports $record */
            $record->email = $email;
            $record->payment = isset ($this->payment[$partnerId]) ? true : false;
            $record->repayment = isset ($this->repayment[$partnerId]) ? true : false;
            $record->partner_id = $partnerId;
            $record->save();
        }
    }


}