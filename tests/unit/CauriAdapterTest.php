<?php

use app\services\payment\banks\CauriAdapter;
use app\services\payment\forms\RefundPayForm;
use app\services\payment\models\PartnerBankGate;
use app\services\payment\models\PaySchet;

class CauriAdapterTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    protected function _before()
    {

    }

    protected function _after()
    {
        //
    }
}