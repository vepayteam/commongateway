<?php

/* @var $IdSchet */
/* @var string $draft */
/* @var string $uslinfo */

?>

<body style="margin:0; padding:0; background:#f5f5f5; -webkit-font-smoothing:antialiased; -webkit-text-size-adjust:none;">
<table width="100%" border="0" cellspacing="0" cellpadding="0">
<tbody>
<tr>
<td bgcolor="#F5F5F5" align="center">
<table width="100%" border="0" cellspacing="0" cellpadding="0">
<tbody>
<tr>
<td align="center">
<table width="700" border="0" cellspacing="0" cellpadding="0">
<tbody>
<tr>
<td bgcolor="#FFFFFF">
    <!-- header -->
    <table width="700" border="0" cellspacing="5" cellpadding="5">
    <tbody>
    <tr><td width="370">
        <table width="202" border="0" cellspacing="3" cellpadding="10"><tbody>
        <tr><td style="font-family:Arial, Helvetica, sans-serif; font-size:25px; color:#505050; font-weight:bold;">
        <a href="https://qroplata.ru" target="_blank" style="color:#505050;"><img style="display:block; border:none;" src="http://qroplata.ru/imgs/logo_small.png" border="0" alt="qroplata.ru"></a>
        </td></tr>
        </tbody></table>
    </td>
    <td width="260">
        <table width="260" border="0" cellspacing="0" cellpadding="0">
        <tbody>
        <tr><td align="right" style="font-family:Arial, Helvetica, sans-serif; line-height:25px; vertical-align:middle; font-size:26px; color: #2d3237;">8 (8332) 35-10-02</td></tr>
        <tr><td align="right" style="font-family:Arial, Helvetica, sans-serif; line-height:25px; vertical-align:middle; font-size:13px; color: #2d3237;">пн-пт с 9:00 до 18:00</td></tr>
        </tbody>
        </table>
    </td>
    </tr>
    </tbody>
    </table>
</td>
</tr>
<tr>
<td>
<!-- main content -->
    <table width="700" border="0" cellspacing="0" cellpadding="0" bgcolor="#FFFFFF" style="font-family: Arial, Helvetica, sans-serif; color:#505050;">
    <tbody>
    <!--text block-->
    <tr>
    <td style="border-top: 1px solid #b4b4b4">
        <table width="660" border="0" cellspacing="0"
        cellpadding="0">
        <tbody>
        <tr><td height="30"></td></tr>
        <tr>
        <td width="20"></td>
        <td width="620" style="font-family: Arial, Helvetica, sans-serif; font-size:24px; line-height:31px; color:#505050; font-weight:bold;">
        Оплата заказа № <?= $IdSchet ?>: успешно завершена
        </td>
        <td width="20"></td>
        </tr>
        <tr><td height="8"></td></tr>
        <tr>
        <td width="20"></td>

        </tbody>
        </table>
    </td>
    </tr>

    <tr>
    <td>
    <table border="0" cellspacing="0" cellpadding="0">
    <tbody>
    <tr>
    <td width="700">
        <!--information block-->
        <table width="100%" border="0" cellspacing="0" cellpadding="0">
        <tbody>
        <tr>
        <td width="130"></td>
        <td width="400" bgcolor="#E8E8E8">
            <table width="100%" border="0" cellspacing="0" cellpadding="0">
            <tbody>
            <tr>
            <td width="20"></td>
            <td width="360">
                <br/>
                <h3>Электронный чек</h3>
                <?=$draft?>
                <br/>
            </td>
            <td width="20"></td>
            </tr>
            </tbody>
            </table>
        </td>
        <td width="130"></td>
        </tr>
        </tbody>
        </table>
    </td>
    </tr>
    </tbody>
    </table>
    </td>
    </tr>
        
    <!--banner-->
    <tr><td height="30"></td></tr>    
    <tr>
    <td style="border-top: 1px solid #b4b4b4">
        <table width="660" border="0" align="center" cellspacing="0" cellpadding="0" bgcolor="#fff">
        <tbody>
        <tr><td colspan="3" height="30"></td></tr>
        <tr>
        <td colspan="3">
        <div>Установи мобильное приложение QR-Оплата и оплачивай по штрих-коду</div>
        <br>
        </td>
        </tr>
        <tr>
            <td height="30" width="110"><a href="https://qroplata.ru/app?android" target="_blank"><img src="http://qroplata.ru/imgs/app_links/play_store.png" width="100" height="30" border="0" alt=""></a></td>
            <td height="30" width="110"><a href="https://qroplata.ru/app?ios" target="_blank"><img src="http://qroplata.ru/imgs/app_links/app_store.png" width="100" height="30" border="0" alt=""></a></td>
            <td height="30"></td>
        </tr>
        </tbody>
        </table>
    </td>
    </tr>
    
    <tr><td height="30"></td></tr>    
    <tr>
    <td style="border-top: 1px solid #b4b4b4">
    <!--info block-->
        <table border="0" cellspacing="0" cellpadding="0">
        <tbody>
        <tr><td height="30"></td></tr>
        <tr>
        <td width="640">
        <table width="100%" border="0" cellspacing="0" cellpadding="0" style="color:#505050;">
        <tbody>
        <tr style="font-size:13px; line-height:20px">
        <td width="20"></td>
        <td style="font-family:Arial, Helvetica, sans-serif; font-size:13px; line-height:20px; color:#505050;">
            <?=(!empty($uslinfo) ? '<div>'.$uslinfo.'</div>' : '') ?>
            <div>Получить информацию о статусе вашего платежа и задать вопросы специалистам службы поддержки вы можете в <a href="https://qroplata.ru/#contact" target="_blank">сервисе поддержки плательщиков QR-Оплата.</a></div>
            <div>Обращаем ваше внимание: Vepay не предоставляет консультации по условиям, срокам доставки и качеству оплаченных товаров и услуг.</div>
        </td>
        <td width="20"></td>
        </tr>
        </tbody>
        </table>
        </td>
        </tr>
        <tr>
        <td height="30"></td>
        </tr>
        <!--end info block-->
        </tbody>
        </table>
    </td>
    </tr>    
    <tr><td height="30"></td></tr>
    </tbody>
    </table>
</td>
</tr>
</table>
</td>
</tr>
</tbody>
</table>
</body>