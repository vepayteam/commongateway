<?php

use yii\helpers\Json;

/**
 * @var string $redirectUrl
 */
?>

<script>
    window.top.document.location.href = <?= Json::encode($redirectUrl) ?>;
</script>
