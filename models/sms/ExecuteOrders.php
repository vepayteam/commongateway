<?php


namespace app\models\sms;


use app\models\bank\TCBank;
use app\models\crypt\CardToken;
use app\models\Payschets;
use app\models\sms\tables\Sms;
use app\models\TU;
use Yii;
use yii\db\ActiveRecord;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\helpers\VarDumper;

/**
 * @property TCBank  $bankClient
 * @property Sms     $sms
 * @property array   $errors
 * @property array[] $records
 * @property Payschets $payschets
 */
class ExecuteOrders implements IExecuteOrders
{
    public $code;
    private $bankClient;
    private $sms;
    private $errors;
    private $records; //заменяет $record;
    private $recordQuery = false;
    private $sendingOrders = false;
    private $payschets;

    public function __construct(string $code, Sms $smsModel, TCBank $bank, Payschets $payschets)
    {
        $this->code = $code;
        $this->sms = $smsModel;
        $this->bankClient = $bank;
        $this->payschets = $payschets;
        $this->records = $this->records($this->orders()); // это не логика это голые запросы.
    }

    /**
     * Пользовать этим вместо основного конструктора.
     *
     * @param string $code - шестизначный код.
     *
     * @return ExecuteOrders
     */
    public static function build(string $code): self
    {
        /**@var Sms $model*/
        $model = Sms::find()->where([
            'code' => $code,
            'partner_id' => Yii::$app->user->identity->getPartner()
        ])->one();
        return new self($code, $model, new TCBank(), new Payschets());
    }

    /**
     * Формирует все зависимости класса
     *
     * @param ConfirmCode $code
     *
     * @return ExecuteOrders
     */
    public static function buildAjax(ConfirmCode $code): self
    {
        $model = new self($code->code(), $code->sms(), new TCBank(), new Payschets());
        if (!$model->successful()) {
            foreach ($model->errors as $error) {
                Stop::app($error['code'], $error['message']);
            }
        }
        return $model;
    }

    /**
     * @param Sms $sms
     *
     * @return mixed
     */
    private function orders(): array
    {
        if ($this->sms and $this->sms->confirm === 0) {
            $via = $this->sms->via; //array
            $orders = ArrayHelper::getColumn($via, 'order_id');
            return $orders;
        }
        return [];
    }

    /**
     * @param Sms $sms
     */
    public function execute(): void
    {
        if($this->sendingOrders === false){
            $this->sendingOrders = true;
            foreach ($this->records() as $record) {
                switch ($record['IsCustom']) {
                    case TU::$TOSCHET: //разбивается еще на 2 вида.
                        $bankAnswer = $this->executeToAccount($record);
                        break;
                    case TU::$TOCARD:
                        $bankAnswer = $this->executeToCard($record);
                        break;
                }
                if ($this->updateTransaction($record, $bankAnswer)) {
                    $this->updateSchet($record['ID']);
                }
            }
            $this->sms->confirm = 1;
            $this->sms->save();
            if (!$this->records()) {
                $this->addError(400, 'Произошла ошибка. Нет платежных поручений.');
            }
        }
    }

    /**
     * Возвращает ответ банка
    */
    private function executeToCard(array $record): array
    {
        $params = $this->qrParams($record['QrParams']);
        $token = new CardToken();
        $this->bankClient->SetMfoGate(TCBank::$OCTGATE, $record);
        return $this->bankClient->transferToCard([
            'IdPay' => $record['ID'],
            'summ' => $record['SummPay'],
            'CardNum' => $token->GetCardByToken($params[1])
        ]);
//        $this->updateStatus($resp['status']);
    }

    /**
     * Возвращает ответ банка.
    */
    private function executeToAccount(array $record): array
    {
        $params = $this->qrParams($record['QrParams']);
        $this->bankClient->SetMfoGate(TCBank::$SCHETGATE, $record);
        if (count($params) > 4) {
            //Юридеческое лицо
            return $this->bankClient->transferToAccount([
                'IdPay' => $record['ID'],
                'account' => $params[0],
                'bic' => $params[1],
                'name' => $params[2],
                'inn' => $params[3],
                'kpp' => $params[4],
                'descript' => $params[5],
                'summ' => $record['SummPay'],

            ]);
        } else {
            //Физлицо
           return $this->bankClient->transferToAccount([
                'IdPay' => $record['ID'],
                'account' => $params[0],
                'bic' => $params[1],
                'name' => $params[2],
                'descript' => $params[3],
                'summ' => $record['SummPay'],
            ]);
        }
    }

    private function qrParams(string $qr): array
    {
        return explode("|", $qr);
    }

    private function addError(int $code, string $message)
    {
        $this->errors[] = ['code' => $code, 'message' => $message];
    }

    /**
     * Вернет записи у которых есть какая то услуга.
     *
     * @param array $orders массив ордеров взятый из sms_via_orders (SmsOrders)
     * @return array
     * @throws \yii\db\Exception
     */
    private function records(array $orders = null): array
    {
        if (!$this->records and !$this->recordQuery and $orders !== null) {
            $this->recordQuery = true;
            $query = new Query();
            $this->records = $query
                ->select('
                    pay_schet.ID, 
                    uslugatovar.IsCustom, 
                    pay_schet.SummPay, 
                    pay_schet.QrParams,
                    partner.LoginTkbOct,
                    partner.KeyTkbOct
                ')
                ->from('pay_schet')
                ->where(['pay_schet.ID' => $orders, 'pay_schet.Status' => 0])
                ->join('LEFT JOIN', 'uslugatovar', 'uslugatovar.ID = pay_schet.IdUsluga')
                ->join('LEFT JOIN', 'partner', 'uslugatovar.IDPartner = partner.ID')
                ->createCommand()
                ->queryAll();
        }
        return $this->records;
    }

    /**
     * Говори о том удачно ли прошла отправка ордера на оплату.
     */
    public function successful(): bool
    {
        if (!$this->sendingOrders) {
            $this->execute();
        }
        if ($this->errors) {
            return false;
        }
        return true;
    }

    /**
     * Возвращает сообщение при удачной работе класса.
     */
    public function successfulMessage(): string
    {
        return "Платежное поручение удачно отправлено";
    }

    /**
     * Возвращает ошибки которые возникли в ходе выполнения задач
     */
    public function errors(): array
    {
        return $this->errors;
    }

    private function updateSchet(int $id): void
    {
        Yii::$app->db->createCommand()->update('pay_schet', [
            'sms_accept' => 1,
            'DateLastUpdate' => time()
        ], ['ID' => $id])->execute();
    }

    private function updateTransaction($record, $bankAnswer): bool
    {
        if ($bankAnswer['status']===1){
            $this->payschets->SetBankTransact([
                'idpay'=>$record['ID'],
                'trx_id'=>$bankAnswer['transac'],
                'url'=>''
            ]);
            return true;
        }
        return false;
    }
}