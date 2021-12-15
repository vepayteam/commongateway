<?php

/* @var yii\web\View $this */
/* @var array $reqdata */
/* @var array $data */
/* @var $IsAdmin bool */
/* @var \yii\data\Pagination $pagination */

use yii\helpers\Html;

?>

<?php

$queryLink = http_build_query($reqdata);

?>
<?php if (count($data) > 0) : ?>
<input class='btn btn-white btn-xs' data-action="repeatnotif-batch" data-params="<?=Html::encode($queryLink)?>" type='button' value='Массово повторить запрос'>
<?php endif; ?>

<table class="table table-striped tabledata" style="font-size: 0.9em">
    <thead>
    <tr>
        <th>Операция</th>
        <th>Дата создания</th>
        <th>Адрес запроса</th>
        <th>Дата выполнения</th>
        <th>Результат</th>
        <th>Действия</th>
    </tr>
    </thead>
<?php if (count($data) > 0) : ?>
    <tbody>
        <?php foreach ($data as $row) : ?>
            <tr>
                <td><?=Html::encode($row['IdPay'])?></td>
                <td><?=date("d.m.Y H:i:s", $row['DateCreate'])?></td>
                <td><?=Html::encode(!empty($row['FullReq']) ? $row['FullReq'] : $row['Email'])?></td>
                <td><?=$row['DateSend'] > 1 ? date("d.m.Y H:i:s", $row['DateSend']) : 'в очереди'?></td>
                <td>
                    <div>HTTP code: <?=Html::encode($row['HttpCode'])?></div>
                    <div><code><?=Html::encode($row['HttpAns'])?></code></div>
                </td>
                <td><input class='btn btn-white btn-xs' data-action="repeatnotif" data-id='<?=Html::encode($row['ID'])?>' type='button' value='Повторить запрос'></td>
            </tr>
        <?php endforeach; ?>
    </tbody>

<tfoot>

    <tr>
        <th colspan='6'>

            <a class="btn btn-white btn-xs" target="_blank"
               href="/partner/callback/listexport?<?=Html::encode($queryLink)?>">
                <i class="fa fa-share"></i>&nbsp;Экспорт xls
            </a>
        </th>
    </tr>

    <?php if ($pagination->pageCount > 1) : ?>
        <tr>
            <td colspan="15" class="footable-visible">
                <?php echo \yii\widgets\LinkPager::widget([
                    'pagination' => $pagination,
                    'hideOnSinglePage' => true,
                    'prevPageLabel' => '‹',
                    'nextPageLabel' => '›',
                    'firstPageLabel' => '«',
                    'lastPageLabel' => '»',
                ]); ?>
            </td>
        </tr>
    <?php endif; ?>

</tfoot>
<?php else : ?>
    <tr><td colspan='12' style='text-align:center;'>Операции не найдены</td></tr>
<?php endif; ?>
</table>
