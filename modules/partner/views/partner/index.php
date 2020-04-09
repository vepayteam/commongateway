<?php

/* @var $this View */
/* @var PartnerUsers $PartnerAdmin */
/* @var $partner Partner */
/* @var $uslugi Uslugatovar[] */
/* @var bool $IsAdmin */
/* @var MfoSettings $settings */

$this->title = "контрагенты";

$this->params['breadtitle'] = "Контрагенты";
if ($IsAdmin) {
    $this->params['breadcrumbs'][] = ['label' => 'Контрагенты', 'url' => ['/partner/partner/index']];
    $this->params['breadcrumbs'][] = $partner->Name;
} else {
    $this->params['breadcrumbs'][] = $this->params['breadtitle'];
}

use app\models\mfo\MfoSettings;
use app\models\partner\PartnerUsers;
use app\models\payonline\Partner;
use app\models\payonline\Uslugatovar;
use yii\web\View;

?>

<div class="row">
    <div class="col-lg-12">
        <div class="tabs-container">
            <ul class="nav nav-tabs">
                <li class="active"><a data-toggle="tab" href="#tab-1"> Данные</a></li>
                <?php if ($IsAdmin) : ?>
                    <li class=""><a data-toggle="tab" href="#tab-2"> Услуги</a></li>
                    <li class=""><a data-toggle="tab" href="#tab-3"> Настройки</a></li>
                <?php endif; ?>
                <?php /*if ($IsAdmin || $roleUser == 1) : ?>
                    <li class=""><a data-toggle="tab" href="#tab-4"> Доступ к кабинету</a></li>
                <?php endif;*/ ?>
            </ul>
            <div class="tab-content">
                <div id="tab-1" class="tab-pane active">
                    <div class="panel-body">
                        <?php if ($IsAdmin): ?>
                            <?=$this->render('_okompanii_edit', ['partner' => $partner, 'PartnerAdmin' => $PartnerAdmin]);?>
                        <?php else: ?>
                            <?=$this->render('_okompanii', ['partner' => $partner]);?>
                            <?=$this->render('_kontakts', ['partner' => $partner]);?>
                        <?php endif; ?>
                    </div>
                </div>
                <?php if ($IsAdmin): ?>
                    <div id="tab-2" class="tab-pane">
                        <div class="panel-body">
                            <?=$this->render('_uslugi_list', ['partner' => $partner, 'uslugi' => $uslugi]);?>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($IsAdmin): ?>
                    <div id="tab-3" class="tab-pane">
                        <div class="panel-body">
                            <?=$this->render('_integration_edit', ['partner' => $partner, 'settings' => $settings]);?>
                        </div>
                    </div>
                <?php endif; ?>

                <?php /*if ($IsAdmin || $roleUser == 1) : ?>
                <div id="tab-4" class="tab-pane">
                    <div class="panel-body">
                        <?=$this->render('_users', ['users' => $users, 'partner' => $partner]);?>
                    </div>
                </div>
                <?php endif;*/ ?>

            </div>
        </div>
    </div>
</div>

<?php $this->registerJs('lk.editpartner();') ?>