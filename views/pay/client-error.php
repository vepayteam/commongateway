<?php

use yii\helpers\Json;

/**
 * @var string $message
 * @var string $failUrl
 */
?>

<script>
    window.top.payform.error(<?=Json::encode($message)?>);

    <?php if(isset($failUrl)): ?>
    setTimeout(function () {
        window.top.document.location.href = <?= Json::encode($failUrl) ?>;
    }, 5000);
    <?php endif; ?>
</script>