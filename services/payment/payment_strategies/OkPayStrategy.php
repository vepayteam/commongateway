<?php


namespace app\services\payment\payment_strategies;


use app\clients\tcbClient\TcbOrderNotExistException;
use app\models\antifraud\AntiFraud;
use app\models\bank\BankCheck;
use app\models\payonline\Cards;
use app\models\payonline\Uslugatovar;
use app\models\queue\DraftPrintJob;
use app\models\TU;
use app\services\balance\BalanceService;
use app\services\notifications\NotificationsService;
use app\services\payment\banks\bank_adapter_responses\BaseResponse;
use app\services\payment\banks\bank_adapter_responses\CheckStatusPayResponse;
use app\services\payment\banks\BankAdapterBuilder;
use app\services\payment\exceptions\BankAdapterResponseException;
use app\services\payment\forms\OkPayForm;
use app\services\payment\forms\SetPayOkForm;
use app\services\payment\jobs\RefundPayJob;
use app\services\payment\models\PayCard;
use app\services\payment\models\PaySchet;
use app\services\payment\PaymentService;
use Yii;
use yii\db\Exception;
use yii\db\Query;
use yii\helpers\Json;
use yii\mutex\FileMutex;

class OkPayStrategy
{
    /** @var OkPayForm */
    protected $okPayForm;
    /** @var PaymentService */
    protected $paymentService;

    protected $isCard = false;

    /**
     * OkPayStrategy constructor.
     * @param OkPayForm $okPayForm
     */
    public function __construct(OkPayForm $okPayForm)
    {
        $this->okPayForm = $okPayForm;
        $this->paymentService = Yii::$container->get('PaymentService');
    }

    /**
     * @return PaySchet
     * @throws Exception
     * @throws \app\services\payment\exceptions\GateException
     */
    public function exec()
    {
        $paySchet = $this->okPayForm->getPaySchet();

        $bankAdapterBuilder = new BankAdapterBuilder();
        $bankAdapterBuilder->buildByBank($paySchet->partner, $paySchet->uslugatovar, $paySchet->bank, $paySchet->currency);

        if($paySchet->Status == PaySchet::STATUS_WAITING && $paySchet->sms_accept == 1) {

            try {
                $checkStatusPayResponse = $bankAdapterBuilder->getBankAdapter()->checkStatusPay($this->okPayForm);
            } catch (TcbOrderNotExistException $e) {
                /** @todo Remove, hack for TCB (VPBC-1298). */
                throw new BankAdapterResponseException('Ошибка запроса, попробуйте повторить позднее.');
            }

            // Привязка карты
            if($this->isNeedLinkCard($paySchet, $checkStatusPayResponse)) {
                $this->linkCard($paySchet, $checkStatusPayResponse);
            }
            $this->confirmPay($paySchet, $checkStatusPayResponse);

            if($checkStatusPayResponse->transId) {
                $paySchet->ExtBillNumber = $checkStatusPayResponse->transId;
            }
            $paySchet->Status = $checkStatusPayResponse->status;
            $paySchet->ErrorInfo = $checkStatusPayResponse->message;
            $paySchet->RRN = $checkStatusPayResponse->rrn;
            $paySchet->Operations = Json::encode($checkStatusPayResponse->operations ?? []);

            // xml['orderadditionalinfo']['rc'] это ТКБшный result code TODO может перенести в $checkStatusPayResponse->rcCode?
            if (isset($checkStatusPayResponse->xml['orderadditionalinfo']['rc'])) {
                $paySchet->RCCode = $checkStatusPayResponse->xml['orderadditionalinfo']['rc'];
            } else if ($checkStatusPayResponse->rcCode) {
                $paySchet->RCCode = $checkStatusPayResponse->rcCode;
            }

            $paySchet->save(false);

            $this->getNotificationsService()->sendPostbacks($paySchet);
        } elseif ($paySchet->sms_accept == 1) {
            $q = new Query();
            $count = $q->from('notification_pay')
                ->where([
                    'IdPay' => $paySchet->ID,
                    'TypeNotif' => 2,
                ])
                ->count();

            if($count == 0) {
                $this->getNotificationsService()->addNotificationByPaySchet($paySchet);
            }
        }

        return $paySchet;
    }

    /**
     * @param PaySchet $paySchet
     * @param CheckStatusPayResponse $checkStatusPayResponse
     * @return bool
     */
    protected function isNeedLinkCard(PaySchet $paySchet, CheckStatusPayResponse $checkStatusPayResponse)
    {
        return (
                $paySchet->RegisterCard
                ||
                $checkStatusPayResponse->status != BaseResponse::STATUS_CREATED
                && $paySchet->IdUsluga == Uslugatovar::TYPE_REG_CARD
                ||
                /** @todo Проверить нужен ли этот блок условий, когда есть {@see PaySchet::$RegisterCard} */
                $paySchet->IdUser > 0
                && in_array($paySchet->uslugatovar->IsCustom, [TU::$JKH, TU::$ECOM, TU::$POGASHECOM, TU::$POGASHATF])
            )
            &&
            !empty($checkStatusPayResponse->cardRefId);
    }

    /**
     * @param PaySchet $paySchet
     * @param CheckStatusPayResponse $checkStatusPayResponse
     * @throws \yii\db\Exception
     */
    protected function linkCard(PaySchet $paySchet, CheckStatusPayResponse $checkStatusPayResponse)
    {
        $payCard = new PayCard();
        $number = str_replace(
            " ",
            "",
            $checkStatusPayResponse->cardNumber
        );
        $payCard->bankId =  $checkStatusPayResponse->cardRefId;
        $payCard->number = $number;
        $payCard->expYear = substr($checkStatusPayResponse->expYear, 2, 2);
        $payCard->expMonth = $checkStatusPayResponse->expMonth;
        $payCard->type = Cards::GetTypeCard($number);

        if(!empty($checkStatusPayResponse->cardHolder)) {
            $payCard->holder = $checkStatusPayResponse->cardHolder;
        }

        return $this->paymentService->updateCardExtId($paySchet, $payCard);
    }

    /**
     * @param PaySchet $paySchet
     * @param CheckStatusPayResponse $checkStatusPayResponse
     * @return bool
     * @throws \Exception
     */
    protected function confirmPay(PaySchet $paySchet, CheckStatusPayResponse $checkStatusPayResponse)
    {
        $res = false;
        $mutex = new FileMutex();
        if ($mutex->acquire('confirmPay' . $paySchet->ID)) {
            // TODO: вернуть транзакции
            //только в обработке платеж завершать
            if(!in_array($paySchet->Status, [PaySchet::STATUS_WAITING, PaySchet::STATUS_WAITING_CHECK_STATUS])) {
                return true;
            }

            if ($checkStatusPayResponse->status == BaseResponse::STATUS_DONE) {
                //завершение оплаты и печать чека

                Yii::warning('OkPayStrategy confirmPay isStatusDone');
                //ок
                $setOkPayform = new SetPayOkForm();
                $setOkPayform->paySchet = $paySchet;
                $setOkPayform->loadByCheckStatusPayResponse($checkStatusPayResponse);

                $this->paymentService->setPayOK($setOkPayform);

                if($paySchet->IdOrder > 0) {
                    $this->paymentService->setOrderOk($paySchet);
                }

                //чек пробить
                if (TU::IsInAll($paySchet->uslugatovar->IsCustom)) {
                    Yii::$app->queue->push(new DraftPrintJob([
                        'idpay' => $paySchet->ID,
                        'tovar' => $paySchet->uslugatovar->NameUsluga
                            . (!empty($paySchet->Dogovor) ? ", Договор: " . $paySchet->Dogovor : ''),
                        'tovarOFD' => $paySchet->uslugatovar->NameUsluga,
                        'summDraft' => $paySchet->SummPay + $paySchet->ComissSumm,
                        'summComis' => $paySchet->ComissSumm,
                        'email' => $paySchet->UserEmail,
                        'checkExist' => Yii::$app->request->isConsoleRequest ? true : false
                    ]));
                }

                /**
                 * Колбэки для refund/reverse операций отправлять не нужно
                 */
                if (!$paySchet->isRefund) {
                    //оповещения на почту и колбэком
                    /** @var NotificationsService $notificationsService */
                    $notificationsService = Yii::$container->get('NotificationsService');
                    $notificationsService->addNotificationByPaySchet($paySchet);
                }

                // если регистрация карты, делаем возврат
                // иначе изменяем баланс
                if($paySchet->Bank != 0) {
                    /**
                     * Если операция и так является возвратом, то заново для неё рефанд запускать не надо
                     */
                    if($paySchet->IdUsluga == Uslugatovar::TYPE_REG_CARD && !$paySchet->isRefund) {
                        Yii::$app->queue->push(new RefundPayJob([
                            'paySchetId' => $paySchet->ID,
                            'initiator' => 'OkPayStrategy confirmPay',
                        ]));
                    } else {
                        /** @var BalanceService $balanceService */
                        $balanceService = Yii::$container->get('BalanceService');
                        $balanceService->changeBalance($paySchet);
                    }

                    $BankCheck = new BankCheck();
                    $BankCheck->UpdateLastCheck($paySchet->Bank);

                    $antifraud = new AntiFraud($paySchet->ID);
                    $antifraud->update_status_transaction(1);
                }

                return true;
            } elseif ($checkStatusPayResponse->status != BaseResponse::STATUS_CREATED) {
                Yii::warning('OkPayStrategy confirmPay isStatusDone');
                $this->paymentService->cancelPay($paySchet, $checkStatusPayResponse->message);

                /**
                 * Колбэки для refund/reverse операций отправлять не нужно
                 */
                if (!$paySchet->isRefund) {
                    /** @var NotificationsService $notificationService */
                    $notificationsService = $this->getNotificationsService();
                    $notificationsService->addNotificationByPaySchet($paySchet);
                }

                $antifraud = new AntiFraud($paySchet->ID);
                $antifraud->update_status_transaction(1);

                return false;
            }

            $BankCheck = new BankCheck();
            $BankCheck->UpdateLastWork($paySchet->Bank);
            $mutex->release('confirmPay' . $paySchet->ID);
        }

        return $res;
    }

    /**
     * @return NotificationsService
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\di\NotInstantiableException
     */
    protected function getNotificationsService()
    {
        return Yii::$container->get('NotificationsService');
    }

}
