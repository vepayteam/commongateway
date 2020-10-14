<?php


namespace app\services\payment\banks;


class Banks
{
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
            default:
                throw new \Exception('Ошибка выбора банка');
        }
    }

}
