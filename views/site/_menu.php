<div class="top-bar">
    <!-- Top Bar -->
    <header id="topHead">
        <div class="container text-center">

            <!-- PHONE/EMAIL -->
            <div class="pull-left head-contacts">
                <div class="quick-contact">
                    <i class="fa fa-phone"></i> <?php echo $config['phone']; ?> &bull;
                    <a href="mailto:<?php echo $config['email']; ?>"><?php echo $config['email']; ?></a>
                </div>
                <div class="head-feedback">
                    <a href="/site/formcont/?op=send_common&form_type=feedback" data-toggle="modal"
                       data-target=".ajax_modal_container" class="btn btn-primary btn-xs white"><i
                            class="fsize16 fa fa-commenting-o" aria-hidden="true"></i>Обратная связь</a>
                </div>
            </div>
            <!-- /PHONE/EMAIL -->
        </div>
    </header>

    <!-- TOP NAV -->
    <header id="topNav" class="topHead"><!-- remove class="topHead" if no topHead used! -->
        <div class="container">

            <!-- Mobile Menu Button -->
            <button class="btn btn-mobile" data-toggle="collapse" data-target=".nav-main-collapse">
                <i class="fa fa-bars"></i>
            </button>

            <!-- Logo text or image -->
            <a class="logo" href="/">
                <img src="<?=Yii::getAlias('@img')?>/aassets/images/logo_qr.svg" height="40px" style="height: 40px; width: auto;"
                     alt="QR-оплата"/>
            </a>

            <!-- Top Nav -->
            <div class="navbar-collapse nav-main-collapse collapse pull-right">
                <nav class="nav-main mega-menu">
                    <ul class="nav nav-pills nav-main scroll-menu" id="topMain">
                        <li>
                            <a href="http://teleport.run/site/index">
                                Преимущества Телепорт для агентов
                            </a>
                        </li>
                        <li>
                            <a href="http://teleport.run/site/recipients">
                                Решения для получателей средств
                            </a>
                        </li>
                        <li>
                            <a href="/">
                                QR-оплата
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
            <!-- /Top Nav -->

        </div>
    </header>
</div>

<span id="header_shadow"></span>
