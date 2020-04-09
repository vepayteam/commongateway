<?php
/* @var $config array */
/* @var $this \yii\web\View */

$this->title = 'Наши контакты';

?>

<?= $this->render('_menu', ['config' => $config]); ?>

<!-- WRAPPER -->
<div id="wrapper">

    <section id="contact" class="container margin-top60">
        <div class="row">

            <!-- map -->
            <div class="col-md-8">

                <h2>Мы на карте:</h2>

                <div id="Kontankt" style="height: 340px; width: 100%;"></div>

            </div>
            <!-- /map -->


            <!-- INFO -->
            <div class="col-md-4">

                <h2>Наши контакты</h2>

                <p>
                    C нами можно связаться:
                </p>

                <div class="divider half-margins"><!-- divider -->
                    <i class="fa fa-star"></i>
                </div>

                <p>
                        <span class="block"><strong><i
                                        class="fa fa-map-marker"></i> Адрес:</strong> <?php echo $config['address']; ?></span>
                    <span class="block"><strong><i
                                    class="fa fa-phone"></i> Телефон:</strong> <?php echo $config['phone']; ?></span>
                    <span class="block"><strong><i class="fa fa-envelope"></i> Email:</strong> <a
                                href="mailto:<?php echo $config['email']; ?>"><?php echo $config['email']; ?></a></span>
                </p>

                <div class="divider half-margins"><!-- divider -->
                    <i class="fa fa-star"></i>
                </div>

                <h4 class="font300">Режим работы:</h4>
                <p>
                    <span class="block"><strong>Пн-Пт:</strong> 09:00-18:00</span>
                    <span class="block"><strong>Сб:</strong> Выходной</span>
                    <span class="block"><strong>Вс:</strong> Выходной</span>
                </p>

            </div>
            <!-- /INFO -->
        </div>

    </section>
</div>
<!-- /WRAPPER -->


<?= $this->render('_footer', ['config' => $config]); ?>

<? $this->registerJsFile('https://api-maps.yandex.ru/2.1/?lang=ru-RU&load=package.full'); ?>
<? $this->registerJsFile('/aassets/js/mapoffice.js?v=2'); ?>
