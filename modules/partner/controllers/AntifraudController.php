<?php


namespace app\modules\partner\controllers;


use app\models\antifraud\control_objects\AntiFraudStats;
use app\models\antifraud\partner\AntiFraudModel;
use app\models\antifraud\partner\RefundModel;
use app\models\antifraud\tables\AFRuleInfo;
use app\models\antifraud\tables\AFSettings;
use app\models\partner\admin\Partners;
use app\models\partner\stat\PayShetStat;
use app\models\partner\stat\StatFilter;
use app\models\partner\UserLk;
use Yii;
use yii\data\ArrayDataProvider;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\helpers\VarDumper;
use yii\web\Controller;
use yii\web\Response;

class AntifraudController extends Controller
{
    use SelectPartnerTrait;

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => false,
                        'roles' => ['?'],
                    ],
                    [
                        'allow' => true,
                        'roles' => ['@'],
                        'denyCallback' => function ($rule, $action) {
                            Yii::$app->getResponse()->redirect(Url::toRoute('/partner'), 302)->send();
                            return false;
                        },
                        'matchCallback' => function ($rule, $action) {
                            return UserLk::IsAdmin(Yii::$app->user);
                        }
                    ],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $fltr = new StatFilter();
        $IsAdmin = UserLk::IsAdmin(Yii::$app->user);
        return $this->render('index', [
            'IsAdmin' => $IsAdmin,
            'partnerlist' => $fltr->getPartnersList(),
            'uslugilist' => $fltr->getTypeUslugLiust()
        ]);
    }

    public function actionAjaxIndexList($page = 0)
    {
        if ($page > 0) {
            $page -= 1;
        }
        $data = Yii::$app->request->get();
        $model = new AntiFraudModel();
        $is_admin = UserLk::IsAdmin(Yii::$app->user);
        if ($model->load($data,'') && $model->validate()) {
            $list_provider = $model->getDataProviderList($is_admin, $page);
        }else{
            $list_provider = new ArrayDataProvider();
        }
        return $this->renderPartial('index_list', [
            'list_provider' => $list_provider,
        ]);
    }

    public function actionAllStat()
    {
        $rule_info = new AFRuleInfo();
        $provider = $rule_info->active_provider(0);
        return $this->render('all_stat', ['data_provider'=>$provider]);
    }

    public function actionAjaxModalInfo($user_hash, $transaction_id)
    {
        $stat = new AntiFraudStats($transaction_id);
        $record = $stat->transaction_info($user_hash);
        return $this->renderPartial('_modal_body', ['record'=>$record]);
    }

    public function actionSettings()
    {
        $model = new RefundModel();
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            if (
                $model->load(Yii::$app->request->post(), '') &&
                $model->validate() &&
                $model->save_block_email()
            ) {
                return ['status'=>1, 'message'=> 'Данные удачно сохранены'];
            } else {
                return ['status' => 0, 'data'=> [
                    'errors'=>$model->getErrors(),
                    'first_error'=>array_values($model->getFirstErrors())
                ]];
            }
        }
        $records = AFSettings::find()->asArray()->all();
        $data = ArrayHelper::map($records,'key', 'value');
        return $this->render('settings', ['data'=> $data]);
    }
}