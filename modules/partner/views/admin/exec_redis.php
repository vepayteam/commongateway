<?php
?>

<form action="/partner/admin/exec-redis" method="post">
    <input type="hidden" name="<?=Yii::$app->request->csrfParam; ?>" value="<?=Yii::$app->request->getCsrfToken(); ?>" />
    <div class="form-group">
        <input type="text" name="name" class="form-control" placeholder="Name">
    </div>
    <div class="form-group">
        <textarea name="params" class="form-control" cols="30" rows="10"></textarea>
    </div>
    <button type="submit" class="btn btn-default">Submit</button>
</form>
