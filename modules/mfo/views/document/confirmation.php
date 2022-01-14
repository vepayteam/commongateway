<?php
/**
 * @var Partner $partner
 * @var PaySchet $paySchet
 */

use app\models\payonline\Partner;
use app\services\payment\helpers\PaymentHelper;
use app\services\payment\models\PaySchet;
use yii\helpers\Html;

$imageDir = __DIR__ . '/confirmation';

$dateTime = Yii::$app->formatter->asDatetime($paySchet->DateLastUpdate, 'php: d.m.Y H:i:s');
$sum = Yii::$app->formatter->asCurrency(PaymentHelper::convertToFullAmount($paySchet->SummPay), $paySchet->currency->Code);
?>
<!doctype html>
<html lang="ru">

<head>
    <title>Подтверждение перевода денежных средств</title>

    <style>
        body {
            background-image: url("<?= "{$imageDir}/background.jpg" ?>");
            background-repeat: no-repeat;
            background-image-resize: 4;
            background-image-resolution: from-image;
        }

        @page {
            margin-left: 110px;
        }

        .top {
            padding-top: 170px;
            padding-left: 300px;
            text-align: right;
            height: 150px;
        }

        .content {
            height: 430px;
        }

        .content .title {
            text-align: center;
        }

        .content .main {
            text-indent: 52px;
            text-align: justify;
        }

        .content .card-info {
            margin-top: 33px;
        }

        .bottom {
            z-index: 2;
        }

        .bottom table {
            width: 100%;
            z-index: 2;
        }

        .bottom td {
            vertical-align: top;
            text-align: right;
            z-index: 2;
        }

        .bottom .left {
            width: 170px;
        }

        .bottom .center {
            width: 185px;
        }

        .bottom .right {
            text-align: right;
        }

        #stamp {
            position: absolute;
            top: 780px;
            left: 280px;
            z-index: 1;
        }

        #sign {
            position: absolute;
            top: 780px;
            left: 395px;
            z-index: 3;
        }

        #bottom-line {
            position: absolute;
            top: 1000px;
            left: 105px;
        }
    </style>
</head>

<body>

<div id="stamp"><img src="<?= "{$imageDir}/stamp.png" ?>" width="155px"/></div>
<div id="sign"><img src="<?= "{$imageDir}/sign.png" ?>" width="75px"/></div>
<div id="bottom-line"><img src="<?= "{$imageDir}/bottom-line.png" ?>" width="630px" height="5px"/></div>

<div class="top">
    <?= Html::encode($partner->UrLico) ?>
    <br/>
    <?= Html::encode($partner->SignatoryShortDative) ?>
    <br/>
    <?= Html::encode($partner->UrAdres) ?>
</div>

<div class="content">
    <p class="title">Подтверждение перевода денежных средств</p>
    <p class="main">
        Настоящим письмом подтверждаем, что в рамках взаимодействия по Договору
        № <?= Html::encode($partner->NumDogovor) ?> от <?= Html::encode($partner->DateDogovor) ?>
        (Заявление о присоединении к Условиям организации переводов
        денежных средств посредством Процессингового Центра VEPay
        № <?= Html::encode($partner->NumDogovor) ?> от <?= Html::encode($partner->DateDogovor) ?>),
        совершена транзакция со счета <?= Html::encode($partner->UrLico) ?> в ПС ООО «ПРОЦЕССИНГОВАЯ
        КОМПАНИЯ БЫСТРЫХ ПЛАТЕЖЕЙ» для выдачи займа на карту:
    </p>

    <p class="card-info">
        ФИО клиента: <?= Html::encode($paySchet->FIO) ?>
        <br/>
        Дата и время: <?= $dateTime ?>
        <br/>
        Сумма перевода: <?= $sum ?>
        <br/>
        Номер карты: <?= Html::encode($paySchet->CardNum) ?>
    </p>
</div>

<div class="bottom">
    <table>
        <tr>
            <td class="left">
                Генеральный директор
                <br/>
                <br/>
                М.П.
            </td>
            <td class="center">
                ________
            </td>
            <td class="right">
                Никонов Г.Б.
            </td>
        </tr>
    </table>
</div>


</body>

</html>