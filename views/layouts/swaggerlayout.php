<?php
/**
 * @var $this \yii\web\View
 * @var $content string
 */

use app\assets\SwaggerAsset;
use yii\helpers\Html;

$title = $this->params['title'] ?? 'Vepay API';
?>

<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= Html::encode($title) ?></title>
    <link href="/swagger/swagger-ui.css?v=1" rel="stylesheet">

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

<script src="/swagger/swagger-ui-bundle.js?v=1"></script>
<script src="/swagger/swagger-ui-standalone-preset.js?v=1"></script>

<?= $content ?>

</body>
</html>