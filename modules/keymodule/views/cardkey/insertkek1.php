<?php

/* @var yii\web\View $this */

use yii\web\View;

$this->title = "внесение ключей";

$this->params['breadtitle'] = "Внесение ключей";
$this->params['breadcrumbs'][] = ['label' => 'Настройка ключей', 'url' => ['/partner/cardkey']];
$this->params['breadcrumbs'][] = $this->params['breadtitle'];
?>

<div class="row">
    <div class="col-lg-12">
        <div class="ibox">
            <div class="ibox-title">
                <h5>Внесение ключей</h5>
            </div>
            <div class="ibox-content">
                <form id="insertkey1" class="form-horizontal">
                    <div class="row m-b-xl m-t-sm">
                        <div class="col-sm-12">
                            <div><label>Ключ 1</label></div>
                            <div><input type="text" class="form-control" name="InsertKey[key1]" value="" autocomplete="off"></div>
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
    let insertKey = function () {
        $('#insertkey1').on('submit', function () {

            if (!confirm("Подтвердите занесение ключ?")) return false;

            $.ajax({
                type: "POST",
                url: '/keymodule/cardkey/savekek1',
                data: $('#insertkey1').serialize(),
                beforeSend: function () {
                },
                success: function (data) {
                    if (data.status == 1) {
                        toastr.success("OK", "Ключ 1 внесён");
                        $('#insertkey1').trigger("reset");
                    } else {
                        toastr.error("Ошибка внесения ключа", "Ошибка");
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

<?=$this->registerJs('insertKey();');?>
