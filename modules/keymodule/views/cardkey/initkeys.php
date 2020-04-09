<?php

/* @var yii\web\View $this */

use yii\web\View;

$this->title = "генерация ключей";

$this->params['breadtitle'] = "Генерация ключей";
$this->params['breadcrumbs'][] = ['label' => 'Настройка ключей', 'url' => ['/partner/cardkey']];
$this->params['breadcrumbs'][] = $this->params['breadtitle'];
?>

<div class="row">
    <div class="col-lg-12">
        <div class="ibox">
            <div class="ibox-title">
                <h5>Генерация ключей</h5>
            </div>
            <div class="ibox-content">
                <form id="initkeys" class="form-horizontal">

                    <div class="row m-b-xl m-t-sm">
                        <div class="col-sm-12">
                            <div><label>Число ключей </label></div>
                            <div><input type="text" class="form-control" name="InitKeys[cntkeys]" value="" autocomplete="off" maxlength="5"></div>
                        </div>
                    </div>
                    <div class="row m-b-xl m-t-sm">
                        <div class="col-sm-12">
                            <input type="hidden" name="_csrf" value="<?=Yii::$app->request->getCsrfToken()?>">
                            <div><input type="submit" class="btn btn-primary" value="Занести"></div>
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

<script>
    let changeKey = function () {
        $('#initkeys').on('submit', function () {

            if (!confirm("Подтвердите генерацию ключей?")) return false;

            $.ajax({
                type: "POST",
                url: '/keymodule/cardkey/doinitkeys',
                data: $('#initkeys').serialize(),
                beforeSend: function () {
                },
                success: function (data) {
                    if (data.status == 1) {
                        toastr.success("OK", "Ключи созданы");
                        $('#initkeys').trigger("reset");
                    } else {
                        toastr.error("Ошибка генерации ключей", "Ошибка");
                    }
                },
                error: function () {
                    toastr.error("Ошибка запроса", "Ошибка");
                }
            });

            return false;
        })
    }
</script>

<?=$this->registerJs('changeKey();');?>
