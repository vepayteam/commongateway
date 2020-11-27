<?php


namespace app\services\ident\interfaces;


interface RunaIdentResponseInteface
{
    const RESPONSE_STATUS_DONE = '00001';
    const RESPONSE_STATUS_INIT = '00008';

    const RESPONSE_STATUSES = [
        self::RESPONSE_STATUS_DONE,
        self::RESPONSE_STATUS_INIT,
    ];

}
