<?php

namespace app\services\callbacks;

use app\services\callbacks\forms\ImpayaCallbackForm;
use app\services\payment\banks\ImpayaAdapter;
use app\services\payment\models\PaySchet;

class ImpayaCallbackService
{

    public function execCallback(ImpayaCallbackForm $impayaCallbackForm)
    {
        $paySchet = $impayaCallbackForm->getPaySchet();
        if(in_array($paySchet->Status, [PaySchet::STATUS_DONE, PaySchet::STATUS_ERROR, PaySchet::STATUS_CANCEL])) {
            $status = ImpayaAdapter::convertStatus($impayaCallbackForm->status_id);
            $paySchet->Status = $status;
            $paySchet->ErrorInfo = $impayaCallbackForm->payment_system_status;
            $paySchet->save(false);
        }
    }

}