<?php

/* @var Partner $Partner */
/* @var array $recviz */
/* @var ActSchet $Schet */

use app\models\partner\stat\ActSchet;
use app\models\payonline\Partner;
use app\models\Helper;
use yii\helpers\Html;

// Данные Vepay
$UrLicoPostP = $recviz['name'];
$InnP = $recviz['inn'];
$KppP = $recviz['kpp'];
$BicBank = $recviz['bic'];
$RaschSchet = $recviz['account'];
$KorSchet = $recviz['ks'];
$NameBank = $recviz['bankname'];
$AddrP = '';
$DirP = '_______________________________';
$BuhP = '_______________________________';

$schet = "
	<div style='font-weight: bold; padding-bottom: 15px; text-decoration: underline;'>".$Partner->UrLico."</div>
	<div style='font-weight: bold; padding-bottom: 15px;'>Адрес: ".$Partner->UrAdres."</div>


	<table cellpadding='0' cellspacing='0'  width='100%' class='table-acc'>
		<tr>
			<td>ИНН ".Html::encode($InnP)."</td>
			<td>КПП ".Html::encode($KppP)."</td>
			<td style='vertical-align: bottom;' rowspan='2'>Сч. №</td>
			<td style='vertical-align: bottom;' rowspan='2'>".Html::encode($RaschSchet)."</td>
		</tr>
		<tr>
			<td colspan='2'>Получатель<br>".Html::encode($UrLicoPostP)."</td>
		</tr>
		<tr>
			<td colspan='2' rowspan='2'>Банк получателя<br>".Html::encode($NameBank)."</td>
			<td >БИК</td>
			<td>".Html::encode($BicBank)."</td>
		</tr>
		<tr>
			<td>Сч. №</td>
			<td>".Html::encode($KorSchet)."</td>
		</tr>
	</table>

	<div style='font-weight:bold;padding: 24px 0 6px; font-size: 16px;'>Счет № ".Html::encode($Schet->NumSchet)." от ".date('d.m.Y',$Schet->DateSchet)."</div>

	<table style='width:100%;padding-top:8px; margin-left:-3px;'>
		<tr>
			<td style='font-size: 14px;padding-bottom:5px;'>Поставщик:</td>
			<td style='font-size: 14px;padding-bottom:5px;'>".Html::encode($UrLicoPostP)." ИНН ".Html::encode($InnP).", КПП ".Html::encode($KppP).", ".Html::encode($AddrP)."</td>
		</tr>
		<tr>
			<td style='font-size: 14px;'>Покупатель:</td>
			<td style='font-size: 14px;'>".Html::encode($Partner->UrLico)." ИНН ".Html::encode($Partner->INN)." КПП ".Html::encode($Partner->KPP)."</td>
		</tr>
	</table>
	<div style='padding-top: 20px; font-size: 12px; font-weight:bold;'>Внимание! При оплате необходимо строго придерживаться указанной формулировки в назначении платежа:</div>
	<div style='padding-bottom: 20px;font-size: 14px'>Оплата по счету ".Html::encode($Schet->NumSchet)." от ".date('d.m.Y',$Schet->DateSchet).", ".Html::encode($Schet->Komment).". Без НДС.</div>
	<table cellpadding='1' cellspacing='0' width='100%' class='table-acc' >
		<tr>
			<td style='padding: 10px 5px; text-align: center; vertical-align: middle;font-weight:bold;width:30px;'>№</td>
			<td style='padding: 10px 5px; text-align: center; vertical-align: middle;font-weight:bold;'>Наименование</td>
			<td style='padding: 10px 5px; text-align: center; vertical-align: middle;font-weight:bold;width:70px;'>Кол-во</td>
			<td style='padding: 10px 5px; text-align: center; vertical-align: middle;font-weight:bold;width:60px;'>Ед.</td>
			<td style='padding: 10px 5px; text-align: center; vertical-align: middle;font-weight:bold;width:90px;'>Цена</td>
			<td style='padding: 10px 5px; text-align: center; vertical-align: middle;font-weight:bold;width:90px;'>Сумма</td>
		</tr>
		<tr>
			<td style='text-align: center;  vertical-align: top;'>1</td>
			<td style='vertical-align: top; text-align: left;'>".Html::encode($Schet->Komment)."</td>
			<td style='vertical-align: top; text-align: right;'>1</td>
			<td style='vertical-align: top; text-align: left;'>усл.</td>
			<td style='vertical-align: top; text-align: right;'>".number_format($Schet->SumSchet/100.0, 2, '.', '')."</td>
			<td style='vertical-align: top; text-align: right;'>".number_format($Schet->SumSchet/100.0, 2, '.', '')."</td>
		</tr>
		<tr>
			<td colspan='5' style='text-align: right;'>Итого:</td>
			<td style='text-align: right;'>".number_format($Schet->SumSchet/100.0, 2, '.', '')."</td>
		</tr>
	</table>

	<div style='padding-top: 12.75pt;font-size: 14px'>Всего наименований 1, на сумму ".number_format($Schet->SumSchet/100.0, 2, '.', '')." руб.</div>
	<div style='font-size: 14px'>Сумма прописью: ".Helper::num2strKopText($Schet->SumSchet)."</div>
	<hr style='border-color:black;margin-top:30px;'>
	<div style='width:100%;'>
		<div style='float:left;padding: 15.25pt 0 0 0;font-size: 14px'>Руководитель предприятия____________ (".Html::encode($DirP).")</div>
		<div style='float:right;padding: 15.25pt 0 0 0;font-size: 14px'>Главный бухгалтер____________ (".Html::encode($BuhP).")</div>
	</div>";
?>

<?=$schet?>
