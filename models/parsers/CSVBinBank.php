<?php


namespace app\models\parsers;


use app\models\antifraud\tables\AFBinBanks;
use Yii;
use yii\helpers\VarDumper;

class CSVBinBank
{
    private $path;

    public function __construct() {
        $this->path = Yii::getAlias('@app') . '/models/antifraud/data/binlist-data.csv';
        $this->parse();
    }

    private function parse(){
        if (file_exists($this->path)){
            $handler = fopen($this->path, 'r');
            $i = 0;
            while($data = fgetcsv($handler, 10000, ',')){
                if($i!==0){
                    $bin = $data[0];
                    $bank = $data[1];
                    $country = $data[6];
                    $record = AFBinBanks::find()->where(['bin'=>$bank])->one();
//                    VarDumper::dump($record);
                    if (!$record){
                        $record = new AFBinBanks();
                        $record->bin = $bin;
                        $record->payment_system = $bank;
                        $record->country = $country;
                        $record->save();
                    }
                }
               $i++;
            }
        }

    }
}