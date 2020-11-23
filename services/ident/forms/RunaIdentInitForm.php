<?php


namespace app\services\ident\forms;


use app\services\ident\traits\ErrorModelTrait;
use yii\base\Model;

class RunaIdentInitForm extends Model
{
    use ErrorModelTrait;

    public $passport_series;
    public $passport_number;
    public $name;
    public $surname;
    public $patronymic;
    public $inn;
    public $snils;

    public function rules()
    {
        return [
            [['passport_series', 'passport_number', 'name', 'surname'],'required'],
            ['passport_series', 'string', 'min' => 4, 'max' => 4],
            ['passport_number', 'string', 'min' => 6, 'max' => 6],
            ['inn', 'string', 'min' => 12, 'max' => 12],
            ['snils', 'string', 'min' => 14, 'max' => 14],
            ['snils', 'match', 'pattern' => '/[0-9]{3}\-[0-9]{3}\-[0-9]{3}\s[0-9]{2}/i'],

            [[
                'passport_series',
                'passport_number',
                'name',
                'surname',
                'patronymic',
                'inn',
                'snils',
            ], 'string'],

            ['inn', 'validateInnAndSnils'],
        ];
    }

    public function validateInnAndSnils()
    {
        if(!empty($this->inn) && !empty($this->snils)) {
            $this->addError('inn', 'inn или snils обязательны к заполнению');
        }
    }



}
