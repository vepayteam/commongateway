<?php


namespace app\services\payment\interfaces;


interface Cache3DSv2Interface
{
    const CACHE_PREFIX_AUTH_DATA = 'PaySchet_3DSv2_AuthData_';
    const CACHE_PREFIX_CARD_REF_ID = 'PaySchet_3DSv2_CardRefId_';
    const CACHE_PREFIX_CARD_NUMBER = 'PaySchet_3DSv2_CardNumber_';
    const CACHE_PREFIX_CHECK_DATA = 'PaySchet_3DSv2_CheckData_';
    const CACHE_PREFIX_AUTH_RESPONSE = 'PaySchet_3DSv2_AuthResponse_';
    const CACHE_PREFIX_CRES = 'PaySchet_3DSv2_CRES_';
}
