<?php


namespace app\models\partner\admin;


use app\models\sms\tables\AccessSms;
use phpDocumentor\Reflection\Types\Self_;
use yii\base\Model;
use yii\helpers\VarDumper;
use yii\web\Request;

class SmsConfigForm extends Model
{
    public $publicKey;
    public $secretKey;
    public $idPartner;
    private $answer;

    public function rules(){
        return [
            [['publicKey', 'secretKey', 'idPartner'], 'required'],
            [['publicKey', 'secretKey', 'idPartner'], 'string'],
        ];
    }

    public static function buildAjax(Request $request){
        $model = new self();
        $model->load($request->post(), '');
        if($model->validate()){
            $model->save();
           $model->answer = ['status' => 1];
        }else{
            $model->answer = ['status' => 0, 'error' => $model->errors];
        }
        return $model;
    }

    public function save(){
        $access = AccessSms::find()->where(['partner_id'=>$this->idPartner, 'description'=>'MainSms.ru'])->one();
        if(!$access){
            $access = new AccessSms();
        }
        $access->secret_key = $this->secretKey;
        $access->public_key = $this->publicKey;
        $access->partner_id = $this->idPartner;
        $access->description = 'MainSms.ru';
        $access->save();
    }

    public function answer(){
        return $this->answer;
    }
}