<?php
/* @var $this \yii\web\View */
/* @var $message string */
/* @var $exception \yii\web\HttpException */

if (stripos(\Yii::$app->requestedRoute, "mfo/") === 0 ||
    stripos(\Yii::$app->requestedRoute, "kfapi/") === 0) {
    //если api - json
    $this->context->layout = "nulllayout";
    Yii::$app->response->format = \yii\web\Response::FORMAT_RAW;
    Yii::$app->response->headers->set("Content-Type", "application/json; charset=UTF-8");
    echo '{"name":"'.$exception->getName().'", "message":"'.$message.'", "status":"'.$exception->statusCode.'"}';

} elseif (stripos(\Yii::$app->requestedRoute, "partner") === 0) {
    //кабинет мерчанта
    $this->context->layout = '@app/modules/partner/views/layouts/partner.php';
    echo $this->render('@app/modules/partner/views/error.php');
} else {
    //сайт
    $this->title = 'Ошибка 404';
?>
    <div class="middle-box text-center animated fadeInDown">
        <h1>404</h1>
        <h3 class="font-bold">Страница не найдена</h3>

        <div class="error-desc">
            <?=$message ?? 'Извините, такой страницы не существует.'?><br/>
            <a href="/" class="btn btn-primary m-t">Вернуться на главную</a>
        </div>
    </div>

<?php
}
?>