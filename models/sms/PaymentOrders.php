<?php


namespace app\models\sms;


use Yii;
use yii\base\Model;
use yii\db\Query;
use yii\helpers\VarDumper;

/**
 * Получает на вход строку "[1,2,3,4,5,6]" и возвращает соответствующие пользователю модели ордеров
 * @property string $orders;
 */
class PaymentOrders extends Model
{
    public $orders;

    public function rules()
    {
        return [
            ['orders', 'required'],
            ['orders', 'validateOrders']
        ];
    }

    /**
     * Необходимо использовать этот конструктор при Ajaх
     * Он автоматически вернет ошибку в в формате json
     * ['Привет это ошибка'] - код ответа 400;
     */
    public static function buildAjax(string $formName): self
    {
        $model = new self();
        $model->load(Yii::$app->request->post(), $formName);
        if (!$model->validate()) {
            foreach ($model->errors as $error) {
                Stop::app(400, $error);
            }
        }
        return $model;
    }

    /**
     * Валидатор. Проверяет чтобы:
     * 1. orders - была строка
     * 2. Чтобы была json строка
     * 3. Чтобы элементы массива были типами int (и одномерным массивом)
     */
    public function validateOrders($attribute, $params): bool
    {
        $orders = $this->$attribute;
        if (!is_string($orders)) {//проверка на строку
            $this->addError($attribute, "Входная строка должна быть в формате Json");
            return false;
        }
        $array = json_decode($orders, false);
        if (!json_last_error() === JSON_ERROR_NONE) { //Проверка на json
            $this->addError($attribute, "Не верные данные.");
            return false;
        }
        foreach ($array as $key => $value) {//проверка элемента массива на integer
            if (!is_int($value)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Возвращает модели которые соответствуют данному пользователю.
     */
    public function models(): array
    {
        return $this->validatedRecords();
    }

    /**
     * @return array- записи из базы.
     */
    private function validatedRecords(): array
    {
        $orders = json_decode($this->orders, true);
        $query = new Query();
        return $query
            ->select('ID')
            ->from('pay_schet')
            ->where([
                'IdOrg' => $this->idOrg()
            ])
            ->andWhere(['in', 'ID', $orders])
            ->createCommand()
            ->queryAll(); //почему то all() возвращает не все результаты.
    }

    private function idOrg(): int
    {
        return Yii::$app->user->identity->getPartner();
    }
}