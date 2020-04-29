<?php

use app\models\partner\news\News;

/* @var yii\web\View $this */
/* @var $IsAdmin */
/* @var News[] $news */
/* @var array $alerts */

$this->title = "Кабинет партнера";
?>

<?php if (false): ?>
<div class="alert alert-warning" role="alert">

</div>
<?php endif; ?>

<?php if ($IsAdmin) : ?>
<div class="row m-b-sm m-t-none">
    <div class="col-sm-12">
        <a href="#modal-addnews" data-toggle="modal" class="btn btn-primary">Добавить новость</a>
    </div>
    <?=$this->render('_addnewsmodal')?>
</div>
<?php endif; ?>

<div class="row">

<div class="col-sm-7">
    <div class="ibox float-e-margins">
        <div class="ibox-title">
            <h5>Новости VEPAY</h5>
        </div>
        <div class="ibox-content">
            <div class="feed-activity-list">

                <?php foreach ($news as $onenew): ?>

                    <div class="feed-element">
                        <div>
                            <strong <?= false ? 'style=\'color: red\'' : ''?>><?=\yii\helpers\Html::encode($onenew->Head)?></strong>
                            <?php if ($IsAdmin) : ?>
                                <a class="pull-right" data-id="<?=$onenew->ID?>" data-click="delnews">x</a>
                            <?php endif;?>
                            <div <?= false ? 'style=\'color: red\'' : ''?>><?=str_replace("\r\n", "<br>", \yii\helpers\Html::encode($onenew->Body))?></div>
                            <small class="text-muted"><?=date('d.m.Y H:i', $onenew->DateAdd)?></small>
                        </div>
                    </div>

                <?php endforeach ?>

            </div>
        </div>
    </div>
</div>

<div class="col-sm-5">

    <div class="ibox float-e-margins">
        <div class="ibox-title">
            <h5>Контакты</h5>
        </div>
        <div class="ibox-content">
            <h5>По финансовым вопросам</h5>
            <p>+7 (499) 954-84-95</p>
            <h5>Служба технической поддержки</h5>
            <p>e-mail: <a href="mailto:support@vepay.online">support@vepay.online</a>
        </div>
    </div>
</div>

</div>

<?php $this->registerJs('news.init()', yii\web\View::POS_READY);