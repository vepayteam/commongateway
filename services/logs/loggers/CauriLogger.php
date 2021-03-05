<?php


namespace app\services\logs\loggers;


use Vepay\Gateway\Logger\LoggerInterface;
use Yii;

class CauriLogger implements LoggerInterface
{

    /**
     * @inheritDoc
     */
    public static function trace($message, $category): void
    {
        Yii::warning($message, 'cauri');
    }

    /**
     * @inheritDoc
     */
    public static function info($message, $category): void
    {
        Yii::warning($message, 'cauri');
    }

    /**
     * @inheritDoc
     */
    public static function warning($message, $category): void
    {
        Yii::warning($message, 'cauri');
    }

    /**
     * @inheritDoc
     */
    public static function error($message, $category): void
    {
        Yii::error($message, 'cauri');
    }
}
