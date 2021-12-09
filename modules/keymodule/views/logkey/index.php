<?php

/* @var yii\web\View $this */
/* @var array $uslugilist */
/* @var array $magazlist */
/* @var $partnerlist  */
/* @var $IsAdmin bool */

$this->title = "логи";

$this->params['breadtitle'] = "Логи";
$this->params['breadcrumbs'][] = $this->params['breadtitle'];

use yii\helpers\Html;
use yii\web\View; ?>

    <div class="row">
        <div class="col-sm-12">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <h5>Логи</h5>
                </div>
                <div class="ibox-content">
                    <form class="form-horizontal" id="keyuserlogform">
                        <div class="form-group"><label class="col-sm-2 control-label">Дата</label>
                            <div class="col-sm-10 col-md-6">
                                <div class="input-daterange input-group">
                                    <input type="text" name="datefrom" value="<?=date("d.m.Y")?>" maxlength="10" class="form-control">
                                    <span class="input-group-addon">по</span>
                                    <input type="text" name="dateto" value="<?=date("d.m.Y")?>" maxlength="10" class="form-control">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-offset-2 col-sm-4">
                                <?= Html::hiddenInput('_csrf', Yii::$app->request->csrfToken, ['id' => '_csrf']) ?>
                                <button class="btn btn-sm btn-primary" type="submit">Сформировать</button>
                            </div>
                        </div>
                    </form>

                    <div class="sk-spinner sk-spinner-wave">
                        <div class="sk-rect1"></div>
                        <div class="sk-rect2"></div>
                        <div class="sk-rect3"></div>
                        <div class="sk-rect4"></div>
                        <div class="sk-rect5"></div>
                    </div>
                    <div class="table-responsive" id="keyloglistresult"></div>
                </div>
            </div>
        </div>
    </div>

<script>
    let linklink = null;
    let keyuserlog = function () {
        $('[name="datefrom"],[name="dateto"]').datetimepicker({
            format: 'DD.MM.YYYY',
            showClose: true
        });

        $('#keyuserlogform').on('submit', function () {
            if (linklink) {
                linklink.abort();
            }
            linklink = $.ajax({
                type: "POST",
                url: '/keymodule/logkey/list',
                data: $('#keyuserlogform').serialize(),
                beforeSend: function () {
                    $('#formProfileUser').closest('.ibox-content').toggleClass('sk-loading');
                },
                success: function (data) {
                    $('#formProfileUser').closest('.ibox-content').toggleClass('sk-loading');
                    if (data.status == 1) {
                        $('#keyloglistresult').html(data.data);
                    } else {
                        $('#keyloglistresult').html(data.message);
                    }
                },
                error: function () {
                    $('#formProfileUser').closest('.ibox-content').toggleClass('sk-loading');
                    toastr.error("Ошибка запроса", "Ошибка");
                }
            });
            return false;
        });
    }
</script>

<?php $this->registerJs('keyuserlog();'); ?>