<?php
/**
 * @var $this \yii\web\View
 * @var $url string
 */

use yii\helpers\Json;

?>

<div id="swagger-ui"></div>

<script>
    window.onload = function () {
        window.ui = SwaggerUIBundle({
            url: <?= Json::encode($url) ?>,
            dom_id: '#swagger-ui',
            deepLinking: true,
            presets: [
                SwaggerUIBundle.presets.apis,
                SwaggerUIStandalonePreset
            ],
            plugins: [
                SwaggerUIBundle.plugins.DownloadUrl
            ],
            layout: "StandaloneLayout",
            validatorUrl: null
        });
    }
</script>