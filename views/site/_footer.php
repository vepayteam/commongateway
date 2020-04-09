<?php
/* @var $config array */
/* @var $this \yii\web\View */
?>

<!-- FOOTER -->
<footer>

    <!-- copyright , scrollTo Top -->
    <div class="footer-bar">
        <div class="container">
            <div class="col-md-3 copyright">&copy; <?= date("Y") ?> ООО "Телепорт"</div>
            <div class="col-md-3 confidencialnost">
                <a href="<?=yii\helpers\Url::to('/site/polit')?>">Политика конфиденциальности</a>
            </div>
            <div class="col-md-6">
                <a class="copyright pull-right" id="toTop" href="#topNav">Вернуться наверх <i class="fa fa-arrow-circle-up"></i></a>
            </div>
        </div>
    </div>
    <!-- copyright , scrollTo Top -->


    <!-- footer content -->
    <div class="footer-content">
        <div class="container">
            <div class="row">
                <div class="col-md-3">
                    <div class="padding-top10">
                        <i class="fa fa-map-marker"></i> <?php echo $config['address']; ?>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="padding-top10">
                        <i class="fa fa-phone"></i> Телефон: <?php echo $config['phone']; ?>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="padding-top10">
                        <i class="fa fa-envelope-o"></i> <a href="mailto:<?php echo $config['email']; ?>"><?php echo $config['email']; ?></a>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="padding-top10">
                        <a href="<?=yii\helpers\Url::to('/site/ofert')?>">Условия использования</a>
                    </div>
				</div>
                <div class="col-md-2 text-right">
                    <div>
                        <img style="width: 165px;" src="<?=Yii::getAlias('@img')?>/aassets/images/vs_ms_mir_logo.png" alt="Visa, Mastercard"/>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- footer content -->

</footer>
<!-- /FOOTER -->

<div class="modal fade ajax_modal_container" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content" style="overflow: visible;"></div>
    </div>
</div>
