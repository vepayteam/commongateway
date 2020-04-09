<?php

/* @var yii\web\View $this */
/* @var array $uslugilist */
/* @var array $magazlist */
/* @var $partnerlist  */
/* @var $IsAdmin bool */

$this->title = "пользователи";

$this->params['breadtitle'] = "Пользователи";
$this->params['breadcrumbs'][] = $this->params['breadtitle'];

use app\models\crypt\KeyUsers;
use yii\web\View; ?>

    <div class="row">
        <div class="col-sm-12">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <h5>Пользователи</h5>
                </div>
                <div class="ibox-content">
                    <form class="form-horizontal" id="keyuserlogform">
                        <table class="table table-striped tabledata" style="font-size: 0.9em">
                            <thead>
                            <tr>
                                <th>Пользователь</th>
                                <th>Дата последнего входа</th>
                                <th>Блокировка</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($data as $row) : ?>
                                <tr>
                                    <td><?=$row['Login'].' ('.$row['ID'].')'?></td>
                                    <td><?=date('d.m.Y H:i:s', $row['DateLastLogin'])?></td>
                                    <td><?=$row['IsActive'] ?
                                            'активен ' . ($row['ID'] != \app\models\crypt\UserKeyLk::Id() ? '<a class="lockbtn btn btn-sm btn-default" href="/keymodule/users/lock/'.$row['ID'].'"> Выкл</a>' : '') :
                                            'заблокирован ' . '<a class="lockbtn btn btn-sm btn-default" href="/keymodule/users/unlock/'.$row['ID'].'">  Вкл</a>'?></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </form>

                    <div class="sk-spinner sk-spinner-wave">
                        <div class="sk-rect1"></div>
                        <div class="sk-rect2"></div>
                        <div class="sk-rect3"></div>
                        <div class="sk-rect4"></div>
                        <div class="sk-rect5"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let keyuserlock= function () {
            $('.lockbtn').on('click', function () {
                if (confirm("Подтвердите блокировку/разблокировку?")) {
                    return true;
                }
                return false;
            });
        }
    </script>

<?php $this->registerJs('keyuserlock();'); ?>