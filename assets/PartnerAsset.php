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
class PartnerAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [

        //Inspina
        //'insasset/css/bootstrap.min.css',
        //'insasset/font-awesome/css/font-awesome.css',
        'aassets/fonts/open-sans/open-sans.css',
        'aassets/fonts/font-awesome-master/css/font-awesome.min.css',
        'aassets/fonts/roboto/css/roboto/roboto-fontface.css',

        'insasset/css/plugins/select2/select2.min.css',
        'insasset/css/plugins/toastr/toastr.min.css',
        'insasset/css/animate.css',
        'insasset/css/plugins/iCheck/custom.css',
        'insasset/js/plugins/gritter/jquery.gritter.css',
        'insasset/css/plugins/awesome-bootstrap-checkbox/awesome-bootstrap-checkbox.css',
        //'insasset/css/plugins/datapicker/bootstrap-datepicker3.css',
        //'insasset/css/plugins/clockpicker/clockpicker.css',
        //'insasset/css/plugins/daterangepicker/daterangepicker-bs3.css',
        'insasset/js/plugins/bootstrap-datetimepicker-master/build/css/bootstrap-datetimepicker.css',
        'insasset/css/plugins/dropzone/basic.css',
        'insasset/css/plugins/dropzone/dropzone.css',
        'insasset/css/plugins/jasny/jasny-bootstrap.min.css',
        //Sweet Alert
        'insasset/css/plugins/sweetalert/sweetalert.css',

        'insasset/css/plugins/summernote/summernote.css',
        'insasset/css/plugins/summernote/summernote-bs3.css',

        'insasset/css/plugins/morris/morris-0.4.3.min.css',

        'insasset/css/style.css',

        'aassets/css/partner.css',

        'aassets/plugins/jquery-ui-interactions/jquery-ui.css',

        'aassets/plugins/colorpicker/css/colorpicker.css',
        'aassets/css/partner/jquery.multiselect.css',

        'aassets/datatables/datatables.min.css',
    ];
    public $js = [

        //Main Inspina scripts
        //'insasset/js/jquery-3.1.1.min.js',
        //'insasset/js/bootstrap.min.js',
        'insasset/js/plugins/metisMenu/jquery.metisMenu.js',
        'insasset/js/plugins/slimscroll/jquery.slimscroll.min.js',

        //Peity
        'insasset/js/plugins/peity/jquery.peity.min.js',

        //Custom and plugin javascript
        'insasset/js/inspinia.js',
        //'insasset/js/plugins/pace/pace.min.js',

        //jQuery UI
        //'insasset/js/plugins/jquery-ui/jquery-ui.min.js',

        //GITTER
        'insasset/js/plugins/gritter/jquery.gritter.min.js',

        //Sparkline
        'insasset/js/plugins/sparkline/jquery.sparkline.min.js',

        //Toastr
        'insasset/js/plugins/toastr/toastr.min.js',

        //iCheck
        'insasset/js/plugins/iCheck/icheck.min.js',

        ////Date range use moment.js same as full calendar plugin
        //'insasset/js/plugins/fullcalendar/moment.min.js',

        //Data picker
        //'insasset/js/plugins/datapicker/bootstrap-datepicker.js',
        //'insasset/js/plugins/datapicker/bootstrap-datepicker.ru.min.js',
        'aassets/plugins/moment/moment.js',
        'aassets/plugins/moment/locale/ru.js',
        'insasset/js/plugins/bootstrap-datetimepicker-master/build/js/bootstrap-datetimepicker.js',

        //Date range picker
        //'insasset/js/plugins/daterangepicker/daterangepicker.js',

         //Jasny
        'insasset/js/plugins/jasny/jasny-bootstrap.min.js',

         //DROPZONE
        'insasset/js/plugins/dropzone/dropzone.js',

        //Sparkline
        'insasset/js/plugins/sparkline/jquery.sparkline.min.js',

        //Sweet alert
        'insasset/js/plugins/sweetalert/sweetalert.min.js',

        //Select2
        'insasset/js/plugins/select2/select2.full.min.js',

        //SUMMERNOTE
        'insasset/js/plugins/summernote/summernote.min.js',

        //morris
        'insasset/js/plugins/morris/raphael-2.1.0.min.js',
        'insasset/js/plugins/morris/morris.js',

        'aassets/plugins/jquery.json.min.js',
        'aassets/js/points.js',
        'aassets/js/partner.js',
        'aassets/js/uslugi.js',
        'aassets/js/partner_usl_add_templates.js',
        'aassets/js/partner_usl.js',
        'aassets/plugins/sortable/Sortable.js',
        'aassets/plugins/jquery-ui-interactions/jquery-ui.js',

        'aassets/plugins/colorpicker/js/colorpicker.js',

        //payment-orders
        'aassets/js/payment-orders/list.js',

        'aassets/js/partner/jquery.multiselect.js',
        'aassets/js/partner/select.js',
        'aassets/js/antifraud/antifraud.js',
        'aassets/js/partner/act.js',
        'aassets/js/partner/news.js',
        'aassets/datatables/datatables.min.js',
        'aassets/js/partner/parts_balance.js',

    ];
    public $depends = [
        'app\assets\CommonAsset'
    ];
}
