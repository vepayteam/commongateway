<?php


namespace app\models\parsers;

use app\models\antifraud\tables\AFAsn;
use app\models\payonline\Cards;
use app\services\DeprecatedCurlLogger;
use DOMDocument;
use SimpleXMLElement;
use Yii;
use yii\base\ErrorException;
use yii\helpers\VarDumper;

/**
 * Парсит все asn какой либо страны.
 * @property DOMDocument $dom
 */
class AllAsn
{
    private $link;
    private $country;
    private $fileName;
    private $dom;
    private $content;

    public function __construct(string $codeCountry, string $fileName = null)
    {
        $this->country = $codeCountry;
        $this->link = 'https://ipinfo.io/countries/' . $codeCountry;
        if (is_null($fileName)) {
            $path = Yii::getAlias("@app") . '/models/geolocation/';
            $fileName = $path . 'asn' . strtoupper($codeCountry) . '.json';
            if (!file_exists($path)){
                mkdir($path);
            }
        }
        $this->fileName = $fileName;
        $this->dom = new DOMDocument();
    }

    public function parse(): void
    {
        libxml_use_internal_errors(true);
        $this->dom->loadHTML($this->html());
        $elem =  $this->dom->getElementById('summary')->getElementsByTagName('tr');
        $array = [];
        for($i = 1; $i< $elem->count(); $i++){
            $array[] = [
                'asn'=>$elem->item($i)->childNodes->item(1)->nodeValue,
                'name'=>$elem->item($i)->childNodes->item(3)->nodeValue,
                'numIps'=>$elem->item($i)->childNodes->item(5)->nodeValue,
            ];
        }
        $fp = fopen($this->fileName, 'w+');
        fwrite($fp, json_encode($array));
        fclose($fp);
    }

    public function html(){
        if (is_null($this->content)){
            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_VERBOSE => Yii::$app->params['VERBOSE'] === 'Y',
                CURLOPT_URL => $this->link,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HEADER =>false,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_USERAGENT => 'spider',
                CURLOPT_POST=>false,
            ]);
            $this->content = curl_exec($curl);

            (new DeprecatedCurlLogger(curl_getinfo($curl), $this->link, [], [], Cards::MaskCardLog($this->content)))();

            curl_close($curl);
        }
        return $this->content;
    }

    public function saveToDb(){
        $fp = fopen($this->fileName, 'r');
        $str = fread($fp, filesize($this->fileName));
        $array = json_decode($str,true);
        if (json_last_error() === JSON_ERROR_NONE){
            foreach ($array as $record){
                $asn = AFAsn::find()->where(['asn'=> $record['asn']])->one();
                if (!$asn){
                    $asn = new AFAsn();
                }
                $asn->asn = $record['asn'];
                $asn->provider = $record['name'];
                $asn->num_ips = (float) str_replace(",",'', $record['numIps']);
                $asn->save();
            }
        }else{
            throw new ErrorException('Не удалось прочитать спарсенный файл.');
        }
    }

}