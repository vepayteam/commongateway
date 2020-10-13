<?php


namespace app\services\payment\banks;


class Banks
{
    const TKB_ID = 2;
    const MTS_ID = 3;


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
            default:
                throw new \Exception('Ошибка выбора банка');
        }
    }

}
