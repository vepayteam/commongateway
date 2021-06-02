<?php


namespace app\models\kfapi;


use Yii;

class KfFormPay extends KfPay
{
    const FORM_KEYS = [
        'name',
        'regex',
        'title',
    ];

    public $form = [];

    public function rules()
    {
        $rules = [
            [['form'], 'validateForm', 'on' => [self::SCENARIO_FORM]],
        ];

        return array_merge(parent::rules(), $rules);
    }

    public function validateForm()
    {
        if(!$this->form) {
            $this->addError('form', 'Данные для формы обязательны');
        }

        foreach ($this->form as $item) {
            foreach (self::FORM_KEYS as $formKey) {
                if(!array_key_exists($formKey, $item) || empty($item[$formKey])) {
                    $this->addError('form', 'Некорректо заполнены данные формы');
                    break;
                }
            }
        }
    }

    public function GetPayForm($IdPay)
    {
        if (Yii::$app->params['DEVMODE'] == 'Y') {
            return Yii::$app->params['domain'] . '/pay/form-data/' . $IdPay;
        } elseif (Yii::$app->params['TESTMODE'] == 'Y') {
            return 'https://'.$_SERVER['SERVER_NAME'].'/pay/form-data/' . $IdPay;
        } else {
            return 'https://api.vepay.online/pay/form-data/' . $IdPay;
        }
    }

    public function createFormElements($IdPay)
    {
        foreach ($this->form as $item) {
            $q = sprintf(
                'SELECT COUNT(*) FROM pay_schet_forms WHERE PayschetId=%d AND Name=\'%s\'',
                $IdPay,
                $item['name']
            );
            $data = Yii::$app->db->createCommand($q)->queryScalar();

            if($data) {
                continue;
            }

            $q = sprintf(
                'INSERT INTO `pay_schet_forms`(`PayschetId`, `Name`, `Regex`, `Title`) VALUES (\'%d\', \'%s\', \'%s\', \'%s\')',
                $IdPay,
                $item['name'],
                $item['regex'],
                $item['title']
            );

            Yii::$app->db->createCommand($q)->execute();
        }
    }

}
