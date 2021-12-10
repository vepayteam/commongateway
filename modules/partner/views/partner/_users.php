<?php

/* @var $this \yii\web\View */
/* @var array $users */
/* @var $partner \app\models\payonline\Partner */

use yii\helpers\Html;

?>

<div class="row">
    <div class="m-md">
        <a href="/partner/partner/users-add/<?=Html::encode($partner->ID)?>" class="btn btn-primary">Добавить</a>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-hover">
        <thead>
        <tr>
            <th>Логин</th>
            <th>ФИО</th>
            <th>Дожность</th>
            <th>E-mail</th>
            <th class="text-right">Статус</th>
        </tr>
        </thead>
        <tbody>
        <?php if ($users) : ?>
            <?php foreach ($users as $u) : ?>
                <tr>
                    <td class="project-title"><a href="/partner/partner/users-edit/<?=Html::encode($u->ID)?>"><?=Html::encode($u->Login)?></a></td>
                    <td class="project-title"><?=Html::encode($u->FIO)?></td>
                    <td class="project-title"><?=Html::encode($u->Doljnost)?></td>
                    <td class="project-title"><?=Html::encode($u->Email)?></td>
                    <td class="project-actions"><?=($u->IsActive ?
                            '<span class="label label-info">Активный</span>' :
                            '<span class="label label-warning">Неактивный</span>'
                        )?></td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="4">Нет пользователей</td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
