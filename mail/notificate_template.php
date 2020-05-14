<?php

/* @var $IdSchet */
/* @var string $draft */
/* @var string $uslinfo */

?>
<table width="700" border="0" cellspacing="0" cellpadding="0" bgcolor="#FFFFFF"
       style="font-family: Arial, Helvetica, sans-serif; color:#505050;">
    <tbody>
    <!--text block-->
    <tr>
        <td height="30"></td>
    </tr>
    <tr>
        <td>
            <p>Оплата заказа № <?= $IdSchet ?>: успешно завершена</p>
        </td>
    </tr>

    <tr>
        <td>
            <br/>
            <p>Электронный чек</p>
            <?= $draft ?>
            <br/>
        </td>
        </td>
    </tr>


    <tr>
        <td>&nbsp;</td>
    </tr>

    <tr>
        <td>
            --
            <p style="margin: 0;padding: 0;padding-top: 20px;font-size: 11px;line-height: 13px;font-style: normal;font-weight: normal;color: #C0BECD;">
                ООО "ПРОЦЕССИНГОВАЯ КОМПАНИЯ БЫСТРЫХ ПЛАТЕЖЕЙ"
            </p>
        </td>
    </tr>
    </tbody>
</table>