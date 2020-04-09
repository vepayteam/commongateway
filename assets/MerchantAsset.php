<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\assets;

use yii\web\AssetBundle;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class MerchantAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [

        //FONTS
        'aassets/fonts/open-sans/open-sans.css',
        'aassets/fonts/font-awesome-master/css/font-awesome.min.css',
        'aassets/fonts/roboto/css/roboto/roboto-fontface.css',

        //CORE CSS
        'aassets/plugins/owl-carousel/owl.carousel.css',
        'aassets/plugins/magnific-popup/magnific-popup.css',
        'aassets/css/animate.css',
        //'aassets/css/superslides.css',

        //REVOLUTION SLIDER
        'aassets/plugins/slider.revolution.v4/css/settings.css',

        //THEME CSS
        'aassets/css/essentials.css',
        'aassets/css/layout.css',
        'aassets/css/layout-responsive.css',
        'aassets/css/darkblue.css',
        'aassets/css/site.css',

        'aassets/plugins/eonasdan-bootstrap-datetimepicker/build/css/bootstrap-datetimepicker.css',

        'aassets/css/communal.css'
    ];
    public $js = [

        //Morenizr
        'aassets/plugins/modernizr.min.js',

        //JAVASCRIPT FILES
        'aassets/plugins/jquery.easing.1.3.js',
        'aassets/plugins/jquery.cookie.js',
        'aassets/plugins/jquery.appear.js',
        'aassets/plugins/jquery.isotope.js',
        'aassets/plugins/masonry.js',
        //'aassets/plugins/jquery.slimscroll.js',

        'aassets/plugins/owl-carousel/owl.carousel.js',
        'aassets/plugins/magnific-popup/jquery.magnific-popup.js',
        'aassets/plugins/stellar/jquery.stellar.min.js',

        //REVOLUTION SLIDER
        'aassets/plugins/slider.revolution.v4/js/jquery.themepunch.tools.min.js',
        'aassets/plugins/slider.revolution.v4/js/jquery.themepunch.revolution.min.js',
        'aassets/js/slider_revolution.js',

        'aassets/js/scripts.js',
        'aassets/js/app.js',
        'aassets/js/customvalidation.js',

        'aassets/plugins/moment/moment.js',
        'aassets/plugins/moment/locale/ru.js',
        'aassets/plugins/eonasdan-bootstrap-datetimepicker/build/js/bootstrap-datetimepicker.min.js',

        'aassets/js/communal.js',
    ];
    public $depends = [
        'app\assets\CommonAsset'
    ];
}