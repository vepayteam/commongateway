<?php

namespace app\controllers;

use app\models\payonline\Partner;
use app\models\queue\SendMailJob;
use app\models\site\CheckPay;
use app\models\site\ContactForm;
use app\models\site\PartnerReg;
use app\models\telegram\Telegram;
use Throwable;
use Yii;
use yii\bootstrap\ActiveForm;
use yii\db\StaleObjectException;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class SiteController extends Controller
{
    /**
     * @return array
     */
    public function behaviors(): array
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

    /**
     * @return array
     */
    public function actions(): array
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
    public function actionIndex(): string
    {
        return $this->render('index');
    }

    /**
     * Самостоятельная регистрация через e-mail
     *
     * @return string
     * @throws BadRequestHttpException
     * @throws Throwable
     * @throws StaleObjectException
     */
    public function actionReg(): string
    {
        $email = Yii::$app->request->get('email');

        $partnerReg = new PartnerReg();
        if (!$partnerReg->load([
                'UrState' => Yii::$app->request->get('ur', 0),
                'Email' => $email,
                'EmailCode' => hash('sha1', random_bytes(40)),
                'DateReg' => time(),
                'State' => 0,
                'IdPay' => 0
            ], '') || !$partnerReg->validate()) {
            throw new BadRequestHttpException();
        }

        $oldPartnerRegs = PartnerReg::findAll(['Email' => $email, 'State' => 0]);
        foreach ($oldPartnerRegs as $oldPartnerReg) {
            $oldPartnerReg->delete();
        }

        $partnerReg->save(false);

        $subject = "Регистрация с системе Vepay";
        $content = $this->renderPartial('@app/mail/checkmail', ['PartnerReg' => $partnerReg]);

        Yii::$app->queue->push(new SendMailJob([
            'email' => $partnerReg->Email,
            'subject' => $subject,
            'content' => $content
        ]));

        return $this->render('message', ['message' => 'На указанную электронну почту отправлено регистрационное письмо. Для завершения регистрации перейдите по ссылке.']);
    }

    /**
     * Самостоятельная регистрация - проверка email и занесение данных
     *
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionRegister(): string
    {
        $id = intval(Yii::$app->request->get('id', 0));
        $code = Yii::$app->request->get('code');

        if (!$id || empty($code)) {
            throw new NotFoundHttpException();
        }

        $partnerReg = PartnerReg::findOne(['ID' => $id, 'State' => 0]);
        if (!$partnerReg || $partnerReg->EmailCode != $code) {
            throw new NotFoundHttpException();
        }

        $partner = new Partner();
        $partner->setAttribute('Email', $partnerReg->Email);

        return $this->render('register', [
            'PartnerReg' => $partnerReg,
            'Partner' => $partner
        ]);
    }

    /**
     * Самостоятельная регистрация - сохранить данные
     *
     * @return array
     * @throws NotFoundHttpException
     */
    public function actionRegisterAdd(): array
    {
        $id = intval(Yii::$app->request->post('regid', 0));
        $partnerReg = PartnerReg::findOne(['ID' => $id, 'State' => 0]);

        if (!Yii::$app->request->isAjax || Yii::$app->request->isPjax || !$partnerReg) {
            throw new NotFoundHttpException();
        }

        Yii::$app->response->format = Response::FORMAT_JSON;

        $partner = new Partner();
        $partner->scenario = Partner::SCENARIO_SELFREG;
        $partner->load(Yii::$app->request->post(), 'Partner');
        $partner->setAttribute('Email', $partnerReg->Email);
        if (!$partner->validate()) {
            return ['status' => 0, 'message' => $partner->GetError()];
        }

        $partner->save(false);

        $partnerReg->State = 1;
        $partnerReg->save(false);

        if ($partner->IsMfo) {
            //создание услуг МФО при добавлении
            $partner->CreateUslugMfo();
        } else {
            //создание услуги магазину при добавлении
            $partner->CreateUslug();
        }

        Yii::$app->queue->push(new SendMailJob([
            'email' => 'info@vepay.online',
            'subject' => 'Зарегистрирован контрагент',
            'content' => 'Зарегистрирован контрагент ' . $partner->Name
        ]));

        return ['status' => 1, 'id' => $partner->ID, 'url' => ''];
    }

    /**
     * @return string
     */
    public function actionOferta(): string
    {
        return $this->renderPartial('ofert');
    }

    /**
     * Форма контактов
     * @param string $op
     * @param string $formType
     * @return string|Response
     */
    public function actionFormcont(string $op = '', string $formType = '')
    {
        if (!Yii::$app->request->isAjax) {
            return $this->redirect(['index']);
        }

        $model = new ContactForm();
        $form = $formType == 'feedback' ? 'feedbackmodal' : 'contactmodal';
        return $this->renderAjax('contact/' . $form, [
            'model' => $model,
            'formType' => $formType
        ]);
    }

    /**
     * @return array
     */
    public function actionFeed(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $telegram = new Telegram();
        $tgMessages = $telegram->ReadMesages();
        if ($tgMessages) {
            return ['status' => 1, 'data' => $tgMessages];
        }

        return ['status' => 0];
    }

    /**
     * Отправка сообщения c сайта (AJAX)
     * @return array|Response
     */
    public function actionContactsend()
    {
        if (!Yii::$app->request->isAjax) {
            return $this->redirect(['index']);
        }

        Yii::$app->response->format = Response::FORMAT_JSON;

        $contactForm = new ContactForm();
        $formName = 'ContactForm';

        if (Yii::$app->request->post('ContactFormInline')) {
            $formName = 'ContactFormInline';
        }

        $formParams = Yii::$app->request->post('ContactForm');
        if ($formParams['subject'] && $formParams['subject'] === 'checkpay') {
            $checkPay = new CheckPay();
            $message = $checkPay->check($formParams);

            return ['status' => 1, 'head' => $message['head'], 'message' => $message['mesg']];
        }

        if ($contactForm->load(Yii::$app->request->post(), $formName) && $contactForm->contact(Yii::$app->params['infoEmail'])) {
            return ['status' => 1, 'head' => 'Ваше обращение принято.', 'message' => 'В ближайшее время мы вам направим ответ.'];
        } else {
            return ['status' => 0, 'message' => 'Ошибка: ' . $contactForm->GetError()];
        }
    }
}
