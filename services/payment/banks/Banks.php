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
            case WallettoBankAdapter::$bank:
                return new WallettoBankAdapter();
            case DectaAdapter::$bank:
                return new DectaAdapter();
            case RunaBankAdapter::$bank:
                return new RunaBankAdapter();
            case GratapayAdapter::$bank:
                return new GratapayAdapter();
            case MonetixAdapter::$bank:
                return new MonetixAdapter();
            case ImpayaAdapter::$bank:
                return new ImpayaAdapter();
            case PayloniumAdapter::$bank:
                return new PayloniumAdapter();
            default:
                throw new \Exception('Ошибка выбора банка');
        }
    }
}
