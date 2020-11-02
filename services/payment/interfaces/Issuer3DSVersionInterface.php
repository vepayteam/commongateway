<?php


namespace app\services\payment\interfaces;


interface Issuer3DSVersionInterface
{
    const V_1 = '1.0.0';
    const V_20 = '2.0.0';
    const V_21 = '2.1.0';

    const ALL = [
        self::V_1,
        self::V_20,
        self::V_21,
    ];

    const V_2 = [
        self::V_20,
        self::V_21
    ];

}
