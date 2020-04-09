<?php
/* @var $model \app\models\site\ContactForm*/
use app\models\site\ContactForm;

?>

<div>
    От: <?= $model->name ?>
</div>
<? if (!empty($model->phone)) : ?>
    <div>
        Тлф: <?= $model->phone ?>
    </div>
<? endif; ?>
<div>
    E-mail: <?= $model->email ?>
</div>
<? if ($model->subject) : ?>
    <div>
        Тема: <?= ContactForm::$Subjectes[$model->subject] ?>
    </div>
<? endif; ?>
<br>
<? if ($model->type) : ?>
    <h3>
        <?= ContactForm::$FormTypes[$model->type] ?>
    </h3>
<? endif; ?>
<div>
    <?= $model->body ?>
</div>