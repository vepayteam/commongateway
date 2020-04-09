<?php
/* @var $this \yii\web\View */
/* @var $url string */
?>

<div class="api-default-index">
    <div id="swagger-ui"></div>
</div>

<?php
$this->registerJs("
      // Begin Swagger UI call region
      const ui = SwaggerUIBundle({
        url: \"".$url."\",
        dom_id: '#swagger-ui',
        deepLinking: true,
        presets: [
          SwaggerUIBundle.presets.apis,
          SwaggerUIStandalonePreset
        ],
        plugins: [
          SwaggerUIBundle.plugins.DownloadUrl
        ],
        layout: \"StandaloneLayout\"
      });
      // End Swagger UI call region
      window.ui = ui;
 ");
?>