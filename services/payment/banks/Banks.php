<?php


namespace app\services\payment\banks;


class Banks
{
    const REG_CARD_BY_OUT_ID = 0;
    const TKB_ID = 2;
    const MTS_ID = 3;

    const ADGB_ID = 5;


    /**
     * @param $bankId
     * @return IBankAdapter
     * @throws \Exception
     */
    public static function getBankAdapter($bankId)
    {
        switch ($bankId) {
            case self::TKB_ID:
                return new TKBankAdapter();
            case self::MTS_ID:
                return new MTSBankAdapter();
            case self::ADGB_ID:
                return new ADGroupBankAdapter();
            case RSBankAdapter::$bank:
                return new RSBankAdapter();
            default:
                throw new \Exception('Ошибка выбора банка');
        }
    }

}
