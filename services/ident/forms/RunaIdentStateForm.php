<?php


namespace app\services\ident\forms;


use yii\base\Model;

class RunaIdentStateForm extends Model
{
    public $tid;
    public $attach_smev_response;

    public function rules()
    {
        return [
            [['tid', 'attach_smev_response'], 'required'],
        ];
    }

}
