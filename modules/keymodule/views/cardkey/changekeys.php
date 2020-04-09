<?php

/* @var yii\web\View $this */

use app\models\crypt\UserKeyLk;
use yii\web\View;

$this->title = "замена ключей";

$this->params['breadtitle'] = "Замена ключей";
$this->params['breadcrumbs'][] = ['label' => 'Настройка ключей', 'url' => ['/partner/cardkey']];
$this->params['breadcrumbs'][] = $this->params['breadtitle'];
?>

<div class="row">
    <div class="col-lg-12">
        <div class="ibox">
            <div class="ibox-title">
                <h5>Замена ключей</h5>
            </div>
            <div class="ibox-content">
                <form id="changekeys" class="form-horizontal">
                    <?php if (UserKeyLk::accessKey1()): ?>
                        <div class="row m-b-xl m-t-sm">
                            <div class="col-sm-12">
                                <div><label>Новый Ключ 1</label></div>
                                <div><input type="text" class="form-control" name="ChangeKeys[key1]" value="" autocomplete="off"></div>
                            </div>
                        </div>
                    <?php endif; ?>
                    <?php if (UserKeyLk::accessKey2()): ?>
                        <div class="row m-b-xl m-t-sm">
                            <div class="col-sm-12">
                                <div><label>Новый Ключ 2</label></div>
                                <div><input type="text" class="form-control" name="ChangeKeys[key2]" value="" autocomplete="off"></div>
                            </div>
                        </div>
                    <?php endif; ?>
                    <?php if (UserKeyLk::accessKey3()): ?>
                        <div class="row m-b-xl m-t-sm">
                            <div class="col-sm-12">
                                <div><label>Новый Ключ 3</label></div>
                                <div><input type="text" class="form-control" name="ChangeKeys[key3]" value="" autocomplete="off"></div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="row m-b-xl m-t-sm">
                        <div class="col-sm-12">
                            <input type="hidden" name="_csrf" value="<?=Yii::$app->request->getCsrfToken()?>">
                            <div><input type="submit" class="btn btn-primary" value="Занести"></div>
                        </div>
                    </div>

                    <div class="row m-b-xl m-t-sm">
                        <div class="col-sm-12">
                            <div><input type="button" class="btn btn-primary" id="recryptkeys" value="Замена ключей шифрования"></div>
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

<script>
    let changeKey = function () {
        $('#changekeys').on('submit', function () {

            if (!confirm("Подтвердите внесение ключа для замены?")) return false;

            $.ajax({
                type: "POST",
                url: '/keymodule/cardkey/dochangekeys',
                data: $('#changekeys').serialize(),
                beforeSend: function () {
                },
                success: function (data) {
                    if (data.status == 1) {
                        toastr.success("OK", "Ключи для замены внесены");
                        $('#changekeys').trigger("reset");
                    } else {
                        toastr.error("Ошибка сохранения замены ключей", "Ошибка");
                    }
                },
                error: function () {
                    toastr.error("Ошибка запроса", "Ошибка");
                }
            });

            return false;
        });

        $('#recryptkeys').on('click', function () {

            if (!confirm("Подтвердите замену ключей?")) return false;

            $.ajax({
                type: "POST",
                url: '/keymodule/cardkey/reencryptkeys',
                data: $('#changekeys').serialize(),
                beforeSend: function () {
                    $('#recryptkeys').prop('disabled', true);
                },
                success: function (data) {
                    $('#recryptkeys').prop('disabled', false);
                    if (data.status == 1) {
                        toastr.success("OK", "Ключи шифрования заменены");
                        $('#changekeys').trigger("reset");
                    } else {
                        toastr.error("Ошибка замены ключей шифрования", "Ошибка");
                    }
                },
                error: function () {
                    $('#recryptkeys').prop('disabled', false);
                    toastr.error("Ошибка запроса", "Ошибка");
                }
            });

            return false;
        });
    }
</script>

<?=$this->registerJs('changeKey();');?>
