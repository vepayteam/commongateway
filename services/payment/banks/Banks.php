<?php

namespace app\services\payment\banks;

class Banks
{
    const REG_CARD_BY_OUT_ID = 0;

    /**
     * @param $bankId
     * @return IBankAdapter
     * @throws \Exception
     */
    public static function getBankAdapter($bankId)
    {
        switch ($bankId) {
            case TKBankAdapter::$bank:
                return new TKBankAdapter();
            case MTSBankAdapter::$bank:
                return new MTSBankAdapter();
            case ADGroupBankAdapter::$bank:
                return new ADGroupBankAdapter();
            case BRSAdapter::$bank:
                return new BRSAdapter();
            case CauriAdapter::$bank:
                return new CauriAdapter();
            case FortaTechAdapter::$bank:
                return new FortaTechAdapter();
            case WalletoBankAdapter::$bank:
                return new WalletoBankAdapter();
            case RunaBankAdapter::$bank:
                return new RunaBankAdapter();
            default:
                throw new \Exception('Ошибка выбора банка');
        }
    }
}
