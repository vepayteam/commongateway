<?php

use app\models\site\PartnerReg;

/* @var PartnerReg $PartnerReg */

?>

<style>
    div {
        margin: 5px 0;
    }
</style>
<table width='700'>
    <tr>
        <td>
            <div width='149'>
                <img style='width: 149px;height: 31px;' src='http://cdn.vepay.online/mail/img/logo.png' width='149'/>
            </div>
        </td>
    </tr>
    <tr>
        <td>
            <div>Здравствуйте!</div>
        </td>
    </tr>
    <tr>
        <td>
            <div>Благодарим за регистрацию!</div>
        </td>
    </tr>
    <tr>
        <td>
            <div>Для завершения регистрации перейдите по
                <a href="https://api.vepay.online/site/register?id=<?= $PartnerReg->ID ?>&code=<?= $PartnerReg->EmailCode ?>">ссылке</a>
            </div>
        </td>
    </tr>
    <tr>
        <td>
            <div>_____________________________</div>
            <div>С уважением, сервис Vepay</div>
        </td>
    </tr>
    <tr>
        <td>
            <div>
                Если Вы не регистрировали свой адрес электронной почты и получили это письмо по ошибке, просто
                проигнорируйте его.
            </div>
        </td>
    </tr>
</table>
