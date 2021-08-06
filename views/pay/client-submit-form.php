<?php

use yii\helpers\Json;

/**
 * @var string $method
 * @var string $url
 * @var array $fields
 */
?>

<script>
    window.top.payform.submitForm(
        <?=Json::encode($method)?>,
        <?=Json::encode($url)?>,
        <?=Json::encode($fields)?>
    );
</script>
