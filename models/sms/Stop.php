<?php
namespace app\models\sms;

use function json_encode;
use Yii;
use yii\base\Model;
use yii\web\Response;

/**
 * Created by PhpStorm.
 * User: Vitaly
 * Date: 10.09.2019
 * Time: 6:04
 */
class Stop extends Model
{
    /*
     * Use only with ajax-request
     * This method stopped app
     * */
    public static function app($code, string $response)
    {
        Yii::$app->response->setStatusCode($code);
        Yii::$app->response->data = [$response];
        Yii::$app->response->format=Response::FORMAT_JSON;
        Yii::$app->end();
    }
}