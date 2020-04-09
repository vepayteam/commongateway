<?php


namespace app\models\sms;

use app\models\partner\admin\Partners;
use app\models\partner\PartnerUsers;
use app\models\payonline\Partner;
use app\models\sms\tables\Sms;
use app\models\sms\tables\SmsOrders;
use Yii;
use yii\db\Query;
use yii\helpers\VarDumper;

/**
 * Генерирует, регистрирует сообщение для смс отправки.
 * @property PaymentOrders->models() $orders;
 * @property SmsOrders $smsRelationAr
 */
class Message implements IMessage
{
    private $orders;
    private $code;
    private $smsId;
    private $errors;
    private $smsAR;
    private $smsRelationAR;
    private $phone;

    public function __construct(array $orders, Sms $smsAR, SmsOrders $smsRelationAR)
    {
        $this->smsAR = $smsAR;
        $this->smsRelationAR = $smsRelationAR;
        $this->orders = $orders;
    }

    public static function buildAjax(array $orders)
    {
        $model = new self($orders, new Sms(), new SmsOrders());
        if (!$model->validated()) {
            foreach ($model->errors() as $error) {
                Stop::app($error['code'], $error['message']);
            }
        }
        return $model;
    }

    public function content(): string
    {
        $this->createRelations();
        return 'Код подтверждения: ' . $this->code() . '. Никому не сообщайте его.';
    }

    public function phones(): array
    {
        return [$this->phone()];
    }

    private function code(): string
    {
        if (!$this->code) {
            $this->disableOldSms(Yii::$app->user->identity->getPartner());
            $this->code = rand(100000, 9999999);
        }
        return $this->code;
    }

    private function disableOldSms(int $idOrg): void
    {
        $dbCommand = Yii::$app->db->createCommand();
        $dbCommand->update('sms', ['confirm' => 1], ['partner_id' => $idOrg])->execute();
    }

    /**
     * вернет номер или пустую строку
     * Если вернет пустую строку то
     * обязательно будет ошибка в массиве $this->errors()
     */
    private function phone(): string
    {
        if (!$this->phone) {
            $parnerId = Yii::$app->user->identity->getPartner();
            $partner = Partner::find()->where(['ID' => $parnerId])->one();
            if ($partner->Phone) {
                $this->phone = $partner->Phone;
            } else {
                $this->phone = '';
                $this->addError(400, 'Пожалуйста, укажите в настройках номер телефона.');
            }
        }
        return $this->phone;
    }

    private function smsId(): int
    {
        if (!$this->smsId) {
            $sms = $this->smsAR;
            $sms->code = $this->code();
            $sms->phone = $this->phone();
            $sms->confirm = 0;
            $sms->partner_id = Yii::$app->user->identity->getPartner();
            $sms->save();
            $sms->refresh();
            $this->smsId = $sms->id;
        }
        return $this->smsId;
    }

    /**
     * Т.к. для таблицы pay_schets - нет activerecord нужно вручную создавать все связи.
     */
    private function createRelations(): void
    {
        $ar = get_class($this->smsRelationAR);
        foreach ($this->orders as $model) {
            $via = new $ar();
            $via->sms_id = $this->smsId();
            $via->order_id = $model['ID'];
            $via->save();
            $via->refresh();
        }
    }

    private function addError($code, $message)
    {
        $this->errors[] = ['code' => $code, 'message' => $message];
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function validated(): bool
    {
        if ($this->phone() != '') {
            return true;
        }
        return false;
    }
}