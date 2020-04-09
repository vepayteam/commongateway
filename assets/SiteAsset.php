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
class SiteAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'aassets/fonts/open-sans/open-sans.css',
        'aassets/fonts/font-awesome-master/css/font-awesome.min.css',
        'aassets/fonts/roboto/css/roboto/roboto-fontface.css',

        'aassets/css/site.css',
    ];
    public $js = [
        'aassets/js/site.js',
    ];
    public $depends = [
        'app\assets\InsAsset'
    ];
}