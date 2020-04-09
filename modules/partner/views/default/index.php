<?php
/* @var yii\web\View $this */
/* @var array $news */
$this->title = "Кабинет партнера";
?>

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
                            <strong <?= $onenew['TypeNews'] == 1 ? 'style=\'color: red\'' : ''?>><?=$onenew['HeaderNews']?></strong>
                            <div <?= $onenew['TypeNews'] == 1 ? 'style=\'color: red\'' : ''?>><?=$onenew['TextNews']?></div>
                            <small class="text-muted"><?=date('d.m.Y H:i', $onenew['DateNew'])?></small>
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
            <p>+7 (8332) 35-10-02 доб. 2 (c 9:00 до 18:00)
            <p>факс: 8 (8332) 35-10-02 доб. 3
            <h5>Служба технической поддержки</h5>
            <p>+7 (8332) 35-10-02 доб. 1 (c 9:00 до 18:00)
            <p>e-mail: <a href="mailto:support@vepay.online">support@vepay.online</a>
        </div>
    </div>
</div>

</div>