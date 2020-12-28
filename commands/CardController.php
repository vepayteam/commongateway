<?php


namespace app\commands;

use yii\console\Controller;
use app\services\cards\DeleteOldPan;

/**
 * Class CardController
 * @package app\commands
 */
class CardController extends Controller
{
    /**
     * @throws \Throwable
     * @throws \yii\db\Exception
     */
    public function actionDeleteOldPan(): void
    {
        DeleteOldPan::exec();
    }

}

