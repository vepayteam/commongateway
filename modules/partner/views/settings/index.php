<?php

/* @var yii\web\View $this */
/* @var string $sel */
/* @var integer $IdPartner */
/* @var bool $IsAdmin */

use app\models\mfo\MfoSettings;
use yii\bootstrap\Html;
use yii\web\View;

$this->title = "настройки";

$this->params['breadtitle'] = "Настройки";
$this->params['breadcrumbs'][] = $this->params['breadtitle'];
?>
        <div class="row">
            <div class="col-sm-12">
                <div class="ibox float-e-margins">
                    <div class="ibox-title">
                        <?php if ($IsAdmin) {
                            echo $this->render('_tabs');
                        }
                        ?>
                    </div>
                    <div class="ibox-content">
                        <?=$sel?>
                    </div>
                </div>
            </div>
        </div>

