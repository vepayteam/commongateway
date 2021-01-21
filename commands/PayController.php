<?php


namespace app\commands;

use yii\console\Controller;
use app\services\payment\helpers\FixMerchantRequestAlreadyExists;

/**
 * Class PayController
 * @package app\commands
 */
class PayController extends Controller
{
    public function actionFixmerchantrequestalreadyexists(): void
    {
        FixMerchantRequestAlreadyExists::fix();
    }

}

