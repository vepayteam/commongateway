<?php
/* @var $config array */
/* @var $this \yii\web\View */

$this->title = 'Наши контакты';
?>

<!-- TOP NAV -->
<?= $this->render('_menu', ['config' => $config]); ?>

<!-- WRAPPER -->
<div id="wrapper">
    <section id="oferta" class="container margin-top60">
        <div class="row">
            <?= $this->render('ofert'); ?>
        </div>
    </section>
</div>
<!-- /WRAPPER -->

<div class="contact-modal open">
    <div class="contact-modal__name"><i class="fa fa-map-marker"></i></div>
    <a href="/site/contact" class="contact-modal__body">
        Наши контакты
    </a>
</div>

<?= $this->render('_footer', ['config' => $config]); ?>