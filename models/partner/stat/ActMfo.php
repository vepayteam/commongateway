<?php

namespace app\models\partner\stat;

use app\models\payonline\Partner;
use Yii;

/**
 * This is the model class for table "act_mfo".
 *
 * @property int $ID
 * @property int $IdPartner id partner mfo
 * @property int $NumAct nomer acta
 * @property int $ActPeriod period unixts
 * @property int $CntPerevod chislo perevodov
 * @property int $SumPerevod summa perevodov
 * @property int $ComisPerevod komissia po perevodam
 * @property int $DateCreate data formirovania
 * @property string|null $FileName fail
 * @property int $IsDeleted 1 - udaleno
 * @property int $SumVozvrat summa vozvrata perevodov
 * @property int $CntVyplata chislo vyplat
 * @property int $SumVyplata summa vyplat
 * @property int $ComisVyplata komissia po vyplatam
 * @property int $BeginOstatokPerevod  nachalnyii ostatok po perevodam
 * @property int $BeginOstatokVyplata nachalnyii ostatok po vyplate
 * @property int $EndOstatokPerevod ostatok po perevodam
 * @property int $EndOstatokVyplata ostatok po vyplate
 * @property int $SumPerechislen perechsilennaya summa po perevodam
 * @property int $SumPostuplen postupivshaya summa dlia vydachi
 * @property int $BeginOstatokVoznag
 * @property int $EndOstatokVoznag
 * @property int $SumPodlejUderzOspariv
 * @property int $SumPodlejVozmeshOspariv
 * @property int $SumPerechKontrag
 * @property int $SumPerechObespech
 * @property int $IsPublic
 * @property int $IsOplat
 * @property int $SumSchetComisVyplata
 * @property int $SumSchetComisPerevod
 */
class ActMfo extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'act_mfo';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['IdPartner', 'ActPeriod', 'SumVozvrat', 'CntPerevod', 'SumPerevod', 'ComisPerevod', 'DateCreate',
                'CntVyplata', 'SumVyplata', 'ComisVyplata', 'BeginOstatokPerevod', 'BeginOstatokVyplata',
                'EndOstatokPerevod', 'EndOstatokVyplata', 'SumPerechislen', 'SumPostuplen'], 'required'],
            [['IdPartner', 'NumAct', 'ActPeriod', 'SumVozvrat', 'CntPerevod', 'SumPerevod', 'ComisPerevod', 'DateCreate',
                'IsDeleted', 'CntVyplata', 'SumVyplata', 'ComisVyplata', 'BeginOstatokPerevod', 'BeginOstatokVyplata',
                'EndOstatokPerevod', 'EndOstatokVyplata', 'SumPerechislen', 'SumPostuplen',
                'BeginOstatokVoznag', 'EndOstatokVoznag', 'SumPodlejUderzOspariv', 'SumPodlejVozmeshOspariv',
                'SumPerechKontrag', 'SumPerechObespech', 'SumSchetComisVyplata', 'SumSchetComisPerevod',
                'IsPublic', 'IsOplat'
            ], 'integer'],
            [['FileName'], 'string', 'max' => 250],
            [['IdPartner', 'ActPeriod'], 'unique', 'targetAttribute' => ['IdPartner', 'ActPeriod']],
        ];
    }

    public function SumEditToKop()
    {
        $flds = [
            'SumVozvrat', 'SumPerevod', 'ComisPerevod', 'BeginOstatokPerevod',
            'EndOstatokPerevod', 'SumPerechislen', 'SumPostuplen', 'BeginOstatokVoznag', 'EndOstatokVoznag',
            'SumPodlejUderzOspariv', 'SumPodlejVozmeshOspariv', 'SumPerechKontrag', 'SumPerechObespech'
        ];
        //'SumVyplata', 'ComisVyplata', 'BeginOstatokVyplata', 'EndOstatokVyplata',
        foreach ($flds as $fld) {
            $this->$fld = (int)round($this->$fld * 100.0, 0);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'ID' => 'ID',
            'IdPartner' => 'Id Partner',
            'NumAct' => 'Num Act',
            'ActPeriod' => 'Act Period',
            'CntPerevod' => 'Cnt Perevod',
            'SumPerevod' => 'Sum Perevod',
            'ComisPerevod' => 'Comis Perevod',
            'DateCreate' => 'Date Create',
            'FileName' => 'File Name',
            'IsDeleted' => 'Is Deleted',
            'SumVozvrat' => 'summa vozvrata perevodov',
            'CntVyplata' => 'Cnt Vyplata',
            'SumVyplata' => 'Sum Vyplata',
            'ComisVyplata' => 'Comis Vyplata',
            'BeginOstatokPerevod' => 'Begin Ostatok Perevod',
            'BeginOstatokVyplata' => 'Begin Ostatok Vyplata',
            'EndOstatokPerevod' => 'End Ostatok Perevod',
            'EndOstatokVyplata' => 'End Ostatok Vyplata',
            'SumPerechislen' => 'Sum Perechislen',
            'SumPostuplen' => 'Sum Postuplen',
        ];
    }

    public function GetError()
    {
        $err = $this->firstErrors;
        $err = array_pop($err);
        return $err;
    }

    /**
     * @return Partner|null
     */
    public function getPartner()
    {
        return Partner::findOne(['ID' => $this->IdPartner]);
    }

    /**
     * @return ActSchet|null
     */
    public function getActSchet()
    {
        return ActSchet::findOne(['IdAct' => $this->ID]);
    }
}
