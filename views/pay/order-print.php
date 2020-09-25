<?php
/**
 * @var array $params
 * @var bool $isPage
 */
?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,user-scalable=no,initial-scale=1,maximum-scale=1,minimum-scale=1">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <?php if($isPage): ?>
        <title>Квитанция к заказу № <?=$params['ID']?></title>
        <link rel="stylesheet" href="/aassets/css/order-print.css">
    <?php endif; ?>
</head>
<body>
<div class="bg-wrapper">
    <div class="c-close">
        <div class="c-content"><h1 class="c-h1">Оплата заказа № <?=$params['ID']?>: успешно завершена</h1>
            <h2 class="c-h2">Электронный чек</h2>
            <p class="c-p">
                api.vepay.online<br/>
                ООО «ПРОЦЕССИНГОВАЯ КОМПАНИЯ БЫСТРЫХ ПЛАТЕЖЕЙ» ИНН: 7728487400<br/>

<!--                ЗН ККТ: 1400000000000024 #3917<br/>-->
<!--                Кассовый чек / приход 11.07.2020 12:47:00<br/>-->
<!--                РН ККТ: 0000000400054952<br/>-->
<!--                ФН: 9999078900001341<br/>-->
<!--                Сайт ФНС: www.nalog.ru<br/>-->
<!--                Смена: 7462 Чек: 9516<br/>-->

                Оплата <?=$params['PartnerName']?><br/>
                Сумма: <?=round($params['SummFull'] / 100, 2) ?><br/>
<!--                Адрес покупателя: tikitwikitim@gmail.com<br/>-->
                Итог <?=round($params['SummFull'] / 100, 2) ?><br/>
                ЭЛЕКТРОННО <?=round($params['SummFull'] / 100, 2) ?><br/>
<!--                СНО: УСН доход- расход<br/>-->
<!--                ФД: 3917 ФП: 1060307958<br/>-->
            </p>
        </div>
    </div>
</div>
<?php if($isPage): ?>
    <script>
        window.print();
    </script>
<?php endif; ?>
</body>
</html>
