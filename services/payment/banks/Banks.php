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
    public static function getBankAdapter($bankId): IBankAdapter
    {
        switch ($bankId) {
            case TKBankAdapter::bankId():
                return new TKBankAdapter();
            case MTSBankAdapter::bankId():
                return new MTSBankAdapter();
            case ADGroupBankAdapter::bankId():
                return new ADGroupBankAdapter();
            case BRSAdapter::bankId():
                return new BRSAdapter();
            case CauriAdapter::bankId():
                return new CauriAdapter();
            case FortaTechAdapter::bankId():
                return new FortaTechAdapter();
            case WallettoBankAdapter::bankId():
                return new WallettoBankAdapter();
            case DectaAdapter::bankId():
                return new DectaAdapter();
            case RunaBankAdapter::bankId():
                return new RunaBankAdapter();
            case GratapayAdapter::bankId():
                return new GratapayAdapter();
            case MonetixAdapter::bankId():
                return new MonetixAdapter();
            case ImpayaAdapter::bankId():
                return new ImpayaAdapter();
            case PayloniumAdapter::bankId():
                return new PayloniumAdapter();
            case PaylerAdapter::$bank:
                return new PaylerAdapter();
            default:
                throw new \Exception('Ошибка выбора банка');
        }
    }
}
