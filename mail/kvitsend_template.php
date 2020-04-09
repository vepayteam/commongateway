<?php
/** @var $LinkCode */
/** @var $Period */
/** @var $nameusluga */
?>

<style>
    p{
        margin: 15px 0;
        width: 100%;
    }
</style>
<div><img style='width: 131px;height: 31px;' src='http://qroplata.ru/imgs/logo_small.png' width='131px'/></div><br><br>
<p>Здравствуйте!</p>
<p>В ваш адрес сформирован ЕПД (квитанция) для оплаты коммунальных услуг за <?=date("m.Y", $Period)?> от: <?=$nameusluga?> </p>
<p>Для оплаты квитанции перейдите по этой ссылке <a href='http://qroplata.ru/pay/<?=$LinkCode?>'>http://qroplata.ru/pay/<?=$LinkCode?></a></p><br><br><br>
<br/><br/>
-- <?="\n"?><br/>
<div>QR-Оплата - самый быстрый способ оплаты квитанций </div><br>
<div style='font-size:0.9em;'>Вы подписаны на рассылку электронных квитанций с <a href='http://qroplata.ru'>QR-Оплата </a>. Для того чтобы отказаться от получения квитанций, <a href='http://qroplata.ru/pay/<?=$LinkCode?>/mail'>перейдите по этой ссылке</a></div>
