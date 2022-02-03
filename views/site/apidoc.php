<?php
/**
 * @var $this \yii\web\View
 * @var $url string
 */

use yii\helpers\Json;

?>

<div class="api-default-index">
    <div id="swagger-ui"></div>
</div>

<?php
$js = <<<JS
window.ui = SwaggerUIBundle({
    url: url,
    dom_id: '#swagger-ui',
    deepLinking: true,
    presets: [
        SwaggerUIBundle.presets.apis,
        SwaggerUIStandalonePreset
    ],
    plugins: [
        SwaggerUIBundle.plugins.DownloadUrl
    ],
    layout: "StandaloneLayout"
});
JS;

$jsUrl = Json::encode($url);
$this->registerJs("(function (url) { {$js} } ( {$jsUrl} ));");
?>