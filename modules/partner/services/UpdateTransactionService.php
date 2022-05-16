<?php

namespace app\modules\partner\services;

use app\modules\partner\models\exceptions\UpdateTransactionException;
use app\modules\partner\models\forms\UpdateTransactionForm;
use app\services\notifications\NotificationsService;
use app\services\payment\models\PaySchet;
use yii\base\Component;
use yii\helpers\Json;
use yii\web\User;

class UpdateTransactionService extends Component
{
    /**
     * @var UpdateTransactionForm
     */
    private $updateTransactionForm;

    /**
     * @var User
     */
    private $user;

    public function __construct(UpdateTransactionForm $updateTransactionForm, User $user)
    {
        $this->updateTransactionForm = $updateTransactionForm;
        $this->user = $user;

        parent::__construct();
    }

    /**
     * @return void
     * @throws UpdateTransactionException
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\db\Exception
     * @throws \yii\di\NotInstantiableException
     */
    public function update()
    {
        $paySchet = $this->getPaySchet();

        $changes = $this->getChangedAttributes($paySchet);
        $this->updatePaySchetAttributes($paySchet);
        $this->logChangedAttributes($paySchet, $changes);

        if ($this->updateTransactionForm->sendCallback) {
            \Yii::info('UpdateTransactionService add notification paySchet.ID=' . $paySchet->ID);

            $notificationService = $this->getNotificationsService();
            $notificationService->addNotificationByPaySchet($paySchet);
        }

        if ($this->statusBeenChanged($changes)) {
            $stopRefreshStatusService = new StopRefreshStatusService($paySchet->ID);
            $stopRefreshStatusService->addStopRefreshStatus();
        }
    }

    /**
     * @param array $changes
     * @return bool
     */
    private function statusBeenChanged(array $changes): bool
    {
        return isset($changes['Status']);
    }

    /**
     * @param PaySchet $paySchet
     * @param array $changes
     * @return void
     */
    private function logChangedAttributes(PaySchet $paySchet, array $changes)
    {
        $json = Json::encode($changes, JSON_PRETTY_PRINT);
        \Yii::warning("User {$this->user->getId()} changed paySchet {$paySchet->ID}: {$json}");
    }

    /**
     * @param PaySchet $paySchet
     * @return array
     */
    private function getChangedAttributes(PaySchet $paySchet): array
    {
        $changes = [];
        if ($paySchet->Extid != $this->updateTransactionForm->extId) {
            $changes['Extid'] = [
                'old' => $paySchet->Extid,
                'new' => $this->updateTransactionForm->extId,
            ];
        }

        if ($paySchet->SummPay != $this->updateTransactionForm->paymentAmount) {
            $changes['SummPay'] = [
                'old' => $paySchet->SummPay,
                'new' => $this->updateTransactionForm->paymentAmount,
            ];
        }

        if ($paySchet->MerchVozn != $this->updateTransactionForm->merchantCommission) {
            $changes['MerchVozn'] = [
                'old' => $paySchet->MerchVozn,
                'new' => $this->updateTransactionForm->merchantCommission,
            ];
        }

        if ($paySchet->BankComis != $this->updateTransactionForm->providerCommission) {
            $changes['BankComis'] = [
                'old' => $paySchet->BankComis,
                'new' => $this->updateTransactionForm->providerCommission,
            ];
        }

        if ($paySchet->Status != $this->updateTransactionForm->status) {
            $changes['Status'] = [
                'old' => $paySchet->Status,
                'new' => $this->updateTransactionForm->status,
            ];
        }

        if ($paySchet->ErrorInfo != $this->updateTransactionForm->description) {
            $changes['ErrorInfo'] = [
                'old' => $paySchet->ErrorInfo,
                'new' => $this->updateTransactionForm->description,
            ];
        }

        if ($paySchet->ExtBillNumber != $this->updateTransactionForm->providerId) {
            $changes['ExtBillNumber'] = [
                'old' => $paySchet->ExtBillNumber,
                'new' => $this->updateTransactionForm->providerId,
            ];
        }

        if ($paySchet->Dogovor != $this->updateTransactionForm->contractNumber) {
            $changes['Dogovor'] = [
                'old' => $paySchet->Dogovor,
                'new' => $this->updateTransactionForm->contractNumber,
            ];
        }

        if ($paySchet->RCCode != $this->updateTransactionForm->rcCode) {
            $changes['RCCode'] = [
                'old' => $paySchet->RCCode,
                'new' => $this->updateTransactionForm->rcCode,
            ];
        }

        return $changes;
    }

    /**
     * @param PaySchet $paySchet
     * @return void
     */
    private function updatePaySchetAttributes(PaySchet $paySchet)
    {
        $paySchet->Extid = $this->updateTransactionForm->extId;
        $paySchet->SummPay = $this->updateTransactionForm->paymentAmount;
        $paySchet->MerchVozn = $this->updateTransactionForm->merchantCommission;
        $paySchet->BankComis = $this->updateTransactionForm->providerCommission;
        $paySchet->Status = $this->updateTransactionForm->status;
        $paySchet->ErrorInfo = $this->updateTransactionForm->description;
        $paySchet->ExtBillNumber = $this->updateTransactionForm->providerId;
        $paySchet->Dogovor = $this->updateTransactionForm->contractNumber;
        $paySchet->RCCode = $this->updateTransactionForm->rcCode;
        $paySchet->save(false);
    }

    /**
     * @return PaySchet
     * @throws UpdateTransactionException
     */
    private function getPaySchet(): PaySchet
    {
        $id = $this->updateTransactionForm->id;
        $paySchet = PaySchet::findOne(['ID' => $id]);
        if (!$paySchet) {
            throw new UpdateTransactionException("PaySchet with id=$id not found");
        }

        return $paySchet;
    }

    /**
     * @return NotificationsService
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\di\NotInstantiableException
     */
    private function getNotificationsService(): NotificationsService
    {
        /** @var NotificationsService $notificationService */
        $notificationService = \Yii::$container->get('NotificationsService');
        return $notificationService;
    }
}