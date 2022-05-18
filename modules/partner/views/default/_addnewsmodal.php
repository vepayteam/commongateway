<?php

use yii\helpers\Html;

?>
<div id="modal-addnews" class="modal fade" aria-hidden="true" style="display: none;">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body">
                <h4>Добавить новость</h4>
                <form method="post" class="form-horizontal" id="addnewsform">
                    <div class="form-group row">
                        <div class="col-sm-12">
                            <label class="control-label">Заголовок</label>
                            <input type="text" name="News[Head]" value="" required minlength="3" maxlength="150" class="form-control">
                            <label class="control-label">Текст</label>
                            <textarea name="News[Body]" minlength="3" required class="form-control" rows="10"></textarea>
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="col-sm-12">
                            <?= Html::hiddenInput('_csrf', Yii::$app->request->csrfToken, ['id' => '_csrf']) ?>
                            <input type="submit" id="submitnews" value="Сохранить" class="btn btn-primary">
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>
