<?php $this->beginPage() ?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,user-scalable=no,initial-scale=1,maximum-scale=1,minimum-scale=1">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <link href="/aassets/css/order-ok.css" rel="stylesheet">
</head>
<body>
<?php $this->beginBody() ?>
<div class="bg-wrapper">
    <div class="r-content">
        <div class="r-header">
            <ul class="r-header-breadcrumbs r-header-breadcrumbs-lg">
                <li>Выбор способ оплаты</li>
                <li><img src="/aassets/images/order-ok/arrow.svg" alt="arrow"></li>
                <li>Оплата</li>
                <li><img src="/aassets/images/order-ok/arrow.svg" alt="arrow"></li>
                <li class="active">Результат</li>
            </ul>
            <ul class="r-header-breadcrumbs r-header-breadcrumbs-sm">
                <li>Оплата</li>
                <li>/</li>
                <li class="active">Результат</li>
            </ul>
        </div>

        <?= $content ?>

        <div class="r-footer">
            <div class="r-footer-info"><h3>Гарантия Безопасности</h3>
                <p>Безопаность процессинга платежей подтверждена сертификатом стандарта безопасности данных индустрии
                    платежных карт PCI DSS. Надежность сервиса обеспечивает интелектуальной системой мониторинга
                    мошеннических операций, а также применением 3D Secure - современной технологии безопасности
                    интернет-платежей. Данные вашей карты вводятся на специальной защищенной платежной странице.
                    Передача информации в процессинговую компанию происходит с применением технологии шифрования TLS.
                    Дальнейшая передача инфорамации осуществляется по закрытым банковским каналам, имеющим наивысший
                    уровень надежности. мы никому не передаем данные вашей карты.</p></div>
            <div class="r-footer-img"><img src="/aassets/images/order-ok/visa.svg" alt="Visa"> <img src="/aassets/images/order-ok/mastercard.svg"
                                                                                                    alt="MasterCard"> <img
                    src="/aassets/images/order-ok/miraccept.svg" alt="MirAccept "> <img src="/aassets/images/order-ok/pci-dss.svg" alt="PCI DSS">
            </div>
        </div>
    </div>
</div>
<script src="/aassets/js/order-ok-vendors.js"></script>
<script src="/aassets/js/order-ok.js"></script>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
