<?php


namespace app\models\antifraud\rules;

use app\models\antifraud\rules\interfaces\ISqlRule;
use yii\helpers\VarDumper;

/**
 * @property bool $as_main
 */
trait DataTrait
{
    private $data;

    public function data_trait()
    {
        if (is_null($this->data)) {
            if ($this->as_main) {
                /**@var ISqlRule $sql_obj */
                $sql_obj = $this->sql_obj();
                if ($sql_obj->separate_sql()->createCommand()->rawSql === 'SELECT *') {
                    $data = [];
                } else {
                    $data = $sql_obj->separate_sql()->all();
                }
                if (!$data) {
                    $data = [];
                }
            } else {
                $data = [];
            }
            $this->data = $data;
        }
        return $this->data;
    }
}