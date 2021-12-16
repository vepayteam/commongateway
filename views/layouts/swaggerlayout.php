<?php
/**
 * @var $this \yii\web\View
 * @var $content string
 */

use app\assets\SwaggerAsset;
use yii\helpers\Html;

$title = $this->params['title'] ?? 'Vepay API';

SwaggerAsset::register($this);
?>

<?php $this->beginPage() ?>
    <!DOCTYPE html>
    <html lang="<?= Yii::$app->language ?>">
    <head>
        <meta charset="<?= Yii::$app->charset ?>">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <?= Html::csrfMetaTags() ?>
        <title><?= Html::encode($title) ?></title>
        <?php $this->head() ?>

        <style>
            html {
                box-sizing: border-box;
                overflow: -moz-scrollbars-vertical;
                overflow-y: scroll;
            }

            *,
            *:before,
            *:after {
                box-sizing: inherit;
            }

            body {
                margin: 0;
                background: #fafafa;
            }
        </style>
    </head>
    <body>
    <?php $this->beginBody() ?>

    <?= $content ?>
    <?php $this->endBody() ?>
    </body>
    </html>
<?php $this->endPage() ?>