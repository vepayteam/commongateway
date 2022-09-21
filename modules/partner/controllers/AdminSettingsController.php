<?php

namespace app\modules\partner\controllers;

use app\modules\partner\components\CheckAccessFilter;
use app\modules\partner\models\forms\AdminSettingsBankForm;
use app\modules\partner\services\AdminSettingsService;
use app\services\payment\models\Bank;
use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

class AdminSettingsController extends Controller
{
    /**
     * @var AdminSettingsService
     */
    private $service;

    /**
     * {@inheritDoc}
     * @throws InvalidConfigException
     */
    public function init()
    {
        parent::init();

        $this->service = \Yii::$app->get(AdminSettingsService::class);
    }

    /**
     * {@inheritDoc}
     */
    public function behaviors(): array
    {
        return [
            'checkAccess' => [
                'class' => CheckAccessFilter::class,
                'allowAdmin' => true,
            ],
            'verbFilter' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'index' => ['GET', 'POST'],
                ],
            ],
        ];
    }

    public function actionIndex(): string
    {
        $settingsForm = $this->service->createFrom();
        $bankForms = $this->service->createBankForms();

        if ($settingsForm->load(\Yii::$app->request->post())) {
            Model::loadMultiple($bankForms, \Yii::$app->request->post());

            $valid = $settingsForm->validate();
            if (Model::validateMultiple($bankForms) && $valid) {
                $this->service->save($settingsForm);
                $this->service->saveBanks($bankForms);
                \Yii::$app->session->setFlash('success', "Настройки сохранены.");
            }
        }

        $bankList = ArrayHelper::map(Bank::find()->all(), 'ID', 'Name');
        return $this->render('index', [
            'settings' => $settingsForm,
            'banks' => $bankForms,
            'bankList' => $bankList,
        ]);
    }

    /**
     * @param string|int $id Bank ID.
     * @throws NotFoundHttpException
     */
    public function actionBank($id)
    {
        $bank = Bank::findOne($id);
        if ($bank === null) {
            throw new NotFoundHttpException('Банк не найден.');
        }

        $bankForm = (new AdminSettingsBankForm())->mapBank($bank);

        if ($bankForm->load(\Yii::$app->request->post()) && $bankForm->validate()) {
            $this->service->saveBank($bank, $bankForm);
            \Yii::$app->session->setFlash('success', "Настройки банка сохранены.");
            return $this->refresh();
        }

        return $this->render('bank', [
            'bank' => $bankForm,
        ]);
    }
}