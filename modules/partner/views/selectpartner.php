<?php
/* @var yii\web\View $this */
/* @var array $partners */
/* @var int $all */
/* @var bool $partial */

?>
<div class="row">
    <div class="col-lg-6">
<?php if (!$partial) : ?>
        <div class="ibox">
                <div class="ibox-title">
                    <h5>Выбор мерчанта</h5>
                </div>
            <div class="ibox-content">
<?php else: ?>
                <div class="col-lg-12">
<?php endif; ?>

                <form method="post">
                    <div class="form-group">
                        <label>Выберите мерчант:</label>
                        <select name="IdPartner" class="form-control">
                            <? if ($all) :?>
                                <option value="-1">По всем</option>
                            <? endif; ?>
                            <? foreach ($partners as $part) : ?>
                                <option value="<?= $part->ID ?>"><?= $part->Name ?></option>
                            <? endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <input type="hidden" name="_csrf" value="<?=Yii::$app->request->csrfToken?>">
                        <input type="submit" class="btn btn-primary" value="Выбрать">
                    </div>
                </form>
            </div>
<?php if (!$partial) : ?>
        </div>
<?php endif; ?>
    </div>
</div>
