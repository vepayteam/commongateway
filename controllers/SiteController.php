<?php

namespace app\controllers;

use app\models\api\Reguser;
use app\models\bank\TCBank;
use app\models\kfapi\KfCard;
use app\models\payonline\CreatePay;
use app\models\payonline\Partner;
use app\models\queue\JobPriorityInterface;
use app\models\queue\SendMailJob;
use app\models\site\CheckPay;
use app\models\site\ContactForm;
use app\models\site\PartnerReg;
use app\models\telegram\Telegram;
use Carbon\Carbon;
use Yii;
use yii\bootstrap\ActiveForm;
use yii\db\Exception;
use yii\helpers\Url;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class SiteController extends Controller
{
    public function behaviors()
    {
        if (!YII_ENV_DEV) {
            $behaviors = parent::behaviors();
            $behaviors['pageCache'] = [
                'class' => 'yii\filters\PageCache',
                'only' => ['index', 'contact'],
                'duration' => 3600,
            ];
            return $behaviors;
        } else {
            return parent::behaviors();
        }
    }

    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ]
        ];
    }

    /**
     * @return string
     */
    public function actionIndex()
    {
        $model = new ContactForm();
        return $this->render('index', [
            'config' => Yii::$app->params['info'],
            'model' => $model
        ]);
    }

    /**
     * Самостоятельная регистрация через e-mail
     *
     * @return string
     * @throws BadRequestHttpException
     */
    public function actionReg()
    {
        $email = Yii::$app->request->get('email');
        $urstate = Yii::$app->request->get('ur', 0);

        $PartnerReg = new PartnerReg();
        if ($PartnerReg->load([
            'Email' => $email,
            'EmailCode' => hash('sha1', random_bytes(40)),
            'UrState' => $urstate,
            'DateReg' => time(),
            'State' => 0,
            'IdPay' => 0
        ], '') && $PartnerReg->validate()) {

            $old = PartnerReg::findAll(['Email' => $email, 'State' => 0]);
            foreach ($old as $pregold) {
                $pregold->delete();
            }

            $PartnerReg->save(false);

            $subject = "Регистрация с системе Vepay";
            $content = $this->renderPartial('@app/mail/checkmail', ['PartnerReg' => $PartnerReg]);

            Yii::$app->queue->push(new SendMailJob([
                'email' => $PartnerReg->Email,
                'subject' => $subject,
                'content' => $content
            ]));

            return $this->render('message', ['message' => 'На указанную электронну почту отправлено регистрационное письмо. Для завершения регистрации перейдите по ссылке.']);
        }
        throw new BadRequestHttpException();

    }

    /**
     * Самостоятельная регистрация - проверка email и занесение данных
     *
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionRegister()
    {
        $id = (int)Yii::$app->request->get('id', 0);
        $code = Yii::$app->request->get('code');

        if ($id && !empty($code)) {
            $PartnerReg = PartnerReg::findOne(['ID' => $id, 'State' => 0]);
            if ($PartnerReg && $PartnerReg->EmailCode == $code) {

                $Partner = new Partner();
                $Partner->setAttribute('Email', $PartnerReg->Email);

                return $this->render('register', [
                    'PartnerReg' => $PartnerReg,
                    'Partner' => $Partner
                ]);
            }
        }

        throw new NotFoundHttpException();
    }

    /**
     * Самостоятельная регистрация - сохранить данные
     *
     * @return array
     * @throws Exception
     * @throws NotFoundHttpException
     */
    public function actionRegisterAdd()
    {
        $id = (int)Yii::$app->request->post('regid', 0);
        $PartnerReg = PartnerReg::findOne(['ID' => $id, 'State' => 0]);

        if (Yii::$app->request->isAjax && !Yii::$app->request->isPjax && $PartnerReg) {

            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

            $partner = new Partner();
            $partner->scenario = Partner::SCENARIO_SELFREG;
            $partner->load(Yii::$app->request->post(), 'Partner');
            $partner->setAttribute('Email', $PartnerReg->Email);
            if (!$partner->validate()) {
                return ['status' => 0, 'message' => $partner->GetError()];
            }

            $partner->save(false);

            $PartnerReg->State = 1;
            $PartnerReg->save(false);

            if ($partner->IsMfo) {
                //создание услуг МФО при добавлении
                $partner->CreateUslugMfo();
            } else {
                //создание услуги магазину при добавлении
                $partner->CreateUslug();
            }

            $url = '';
            /*if ($partner->UrState == 2) {
                //привязать карту через тестовый платеж для физлица
                $kfCard = new KfCard();
                $kfCard->scenario = KfCard::SCENARIO_REG;
                $reguser = new Reguser();
                $user = $reguser->findUser('0', $partner->ID.'-'.time(), md5($partner->ID.'-'.time()), $partner->ID, false);
                if ($user) {
                    $pay = new CreatePay($user);
                    $data = $pay->payActivateCard(0, 3, TCBank::$bank, $partner->ID); //Provparams
                    $url = $kfCard->GetRegForm($data['IdPay']);
                }
            }*/

            Yii::$app->queue->push(new SendMailJob([
                'email' => 'info@vepay.online',
                'subject' => "Зарегистрирован контрагент",
                'content' => "Зарегистрирован контрагент " . $partner->Name
            ]));

            return ['status' => 1, 'id' => $partner->ID, 'url' => $url];
        }

        throw new NotFoundHttpException();
    }


    //старое

    /**
     * @return string
     */
    public function actionOfert()
    {
        return $this->render('oferta', [
            'config' => Yii::$app->params['info']
        ]);
    }
    /**
     * @return string
     */
    public function actionOferta()
    {
        return $this->renderPartial('ofert');
    }
    /**
     * @return string
     */
    public function actionPolit()
    {
        return $this->render('polit', [
            'config' => Yii::$app->params['info'],
        ]);
    }

    /**
     * Форма контактов
     * @param string $op
     * @param string $form_type
     * @return string|\yii\web\Response
     */
    public function actionFormcont($op = '', $form_type = '')
    {
        if (Yii::$app->request->isAjax && preg_match('/[\w_]/', $form_type) && preg_match('/[\w_]/', $op)) {

            $model = new ContactForm();
            $form = $form_type == 'feedback' ? 'feedbackmodal' : 'contactmodal';
            return $this->renderAjax('contact/' . $form, [
                'model' => $model,
                'formType' => $form_type
            ]);

        } else {
            return $this->redirect(['index']);
        }

    }

    /**
     * Displays contact page.
     *
     * @return string
     */
    public function actionContact()
    {
        $model = new ContactForm();
        return $this->render('contact', [
            'config' => Yii::$app->params['info'],
            'model' => $model
        ]);

    }

    public function actionFeed()
    {
        $Telegram = new Telegram();
        Yii::$app->response->format = Response::FORMAT_JSON;
        $ret = $Telegram->ReadMesages();
        if ($ret) {
            return ['status' => 1, 'data' => $ret];
        }
        return ['status' => 0];
    }

    /**
     * Отправка сообщения c сайта (AJAX)
     * @return array|\yii\web\Response
     */
    public function actionContactsend()
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            $model = new ContactForm();
            $formName = "ContactForm";
            if (isset(Yii::$app->request->post()['ContactFormInline'])) {
                $formName = "ContactFormInline";
            }

            if (isset(Yii::$app->request->post()['ContactForm']['subject']) &&
                isset(Yii::$app->request->post()['ContactForm']['subject']) == "checkpay") {

                $checkPay = new CheckPay();
                $message = $checkPay->check(Yii::$app->request->post()['ContactForm']);

                return ['status' => 1, 'head' => $message['head'], 'message' => $message['mesg']];

            } else {

                if ($model->load(Yii::$app->request->post(), $formName) &&
                    $model->contact(Yii::$app->params['infoEmail'])) {
                    return ['status' => 1, 'head' => 'Ваше обращение принято.', 'message' => 'В ближайшее время мы вам направим ответ.'];
                } else {
                    $err = ActiveForm::validate($model);
                    if (isset(array_values($err)[0][0])) {
                        $err = array_values($err)[0][0];
                    }
                    return ['status' => 0, 'message' => 'Ошибка: ' . $err];
                }
            }
        }
        return $this->redirect(['index']);
    }

    public function actionTest()
    {
        print_r(Carbon::now()->addDays(-1)->timestamp);
    }

}
