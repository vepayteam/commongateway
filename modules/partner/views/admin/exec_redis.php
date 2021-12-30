<?php

use yii\helpers\Html;
?>

<form action="/partner/admin/exec-redis" method="post">
    <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>
    <div class="form-group">
        <input type="text" name="name" class="form-control" placeholder="Name">
    </div>
    <div class="form-group">
        <textarea name="params" class="form-control" cols="30" rows="10"></textarea>
    </div>
    <button type="submit" class="btn btn-default">Submit</button>
</form>
