<?php


namespace app\models\partner\stat\export\csv;


use app\models\partner\UserLk;
use app\models\TU;
use app\services\payment\helpers\PaymentHelper;
use app\services\payment\models\PaySchet;
use Yii;
use yii\helpers\VarDumper;

/**
 * @property array $list - [ строка1 -> ["col1", "col2", ...], строка2 -> ["col1", "col2", ...]]
 */
class OtchToCSV extends ToCSV
{

    private $payment;
    private $repayment;

    public function __construct(array $list, $payment = null, $repayment = null)
    {
        $this->payment = $payment;
        $this->repayment = $repayment;
        $path = Yii::getAlias('@app/runtime/csv/');
        $filename = time() . '.csv';
        $generator = $this->preparationData($list);

        parent::__construct($generator, $path, $filename);
    }

    protected function header(bool $isAdmin = false): array
    {
        $header_admin = $isAdmin ? [
            'Комис. банка',
            'Возн. Vepay',
        ] : [];

        return array_merge(
            [
                'ID Vepay',
                'ExtID',
                'Код ответа',
                'Услуга',
                'Реквизиты',
                'Договор',
                'ФИО',
                'Сумма',
                'Комиссия',
                'К оплате',
            ],
            $header_admin,
            [
                'Дата создания',
                'Статус',
                'Ошибка',
                'Дата оплаты',
                'Номер транзакции',
                'ID мерчанта',
                'Маска карты',
                'Держатель карты',
                'RRN',
                'Хэш от номера карты',
                'Наименование банка-эквайера',
                ]
        );
    }

    protected function preparationData(array $list): \Generator
    {
        $isAdmin = true;
        if(isset(Yii::$app->user)) {
            $isAdmin = UserLk::IsAdmin(Yii::$app->user);
        }

        $listData = $this->listData($list['data'], $isAdmin);

        yield from array_merge(
            [$this->header($isAdmin)],
            $listData,
            [$this->totalString($list['data'])]
        );
    }

    public function export()
    {
        parent::export();
    }

    protected function totalString(array $list, ?FooterInfo $footerInfo = null): array
    {
        $totalSum = $totalFee = 0;
        foreach ($list as $data) {
            if (
                intval($data['Status']) === PaySchet::STATUS_REFUND_DONE ||
                intval($data['Status']) === PaySchet::STATUS_CANCEL
            ) {
                $totalSum -= (int) $data['SummPay'];
                $totalFee -= (int) $data['ComissSumm'];
            } else {
                $totalSum += (int) $data['SummPay'];
                $totalFee += (int) $data['ComissSumm'];
            }
        }
        return [
            'Итого: ',
            '',
            '',
            '',
            '',
            '',
            '',
            number_format(PaymentHelper::convertToFullAmount($totalSum), 2, ',', ''),
            number_format(PaymentHelper::convertToFullAmount($totalFee), 2, ',', ''),
        ];
    }

    protected function listData($list, bool $isAdmin = false): array
    {
        if ((is_array($list) || $list instanceof \Generator) === false) {
            throw new \InvalidArgumentException('list должен быть массивом или генератором');
        }

        $result = [];

        foreach ($list as $data) {

            if($this->checkData($data)) {
                $ret_admin = $isAdmin ? [
                    number_format($data['BankComis'] / 100.0, 2, '.', ''),
                    number_format($data['VoznagSumm'] / 100.0, 2, '.', ''),
                ] : [];
                $result[] = array_merge(
                    [
                        $data['ID'],
                        $data['Extid'],
                        $data['RCCode'],
                        str_replace('"', "", $data['NameUsluga']),
                        $data['QrParams'],
                        $data['Dogovor'],
                        $data['FIO'],
                        number_format($data['SummPay'] / 100.0, 2, '.', ''),
                        number_format($data['ComissSumm'] / 100.0, 2, '.', ''),
                        number_format(($data['SummPay'] + $data['ComissSumm']) / 100.0, 2, '.', ''),
                    ],
                    $ret_admin,
                    [
                        date("d.m.Y H:i:s", $data['DateCreate']),
                        PaySchet::getStatusTitle($data['Status']),
                        $data['ErrorInfo'],
                        $data['DateOplat'] > 0 ? date("d.m.Y H:i:s", $data['DateOplat']) : '',
                        $data['ExtBillNumber'],
                        $data['IdOrg'],
                        $data['CardNum'],
                        $data['CardHolder'],
                        $data['RRN'],
                        $data['IdKard'],
                        $data['BankName'],
                    ]

                );
            }
        }

        return $result;
    }

    protected function checkData(array $data): bool
    {
        if ($this->payment === null && $this->repayment === null){ //в случае когда делается обычный экспорт.
            return true;
        }
        $cust = $data['IsCustom'];
        //если стоит задача отправить "выдачу" и (тип операции выдача на карту или тип операции выдача на счет)
        if ($this->payment && ($cust == TU::$TOCARD || $cust == TU::$TOSCHET)) {
            return true;
        }
        //если стоит задача отправить "погашшение" и (тип операции погашение AFT или погашение ECOM).
        if ($this->repayment && ($cust == TU::$POGASHATF || TU::$POGASHECOM || TU::$AVTOPLATECOM || TU::$AVTOPLATATF)) {
            return true;
        }
        return false;
    }
}