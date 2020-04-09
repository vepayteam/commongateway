<?php

namespace app\modules\keymodule\controllers;

use app\models\crypt\ChangeKeys;
use app\models\crypt\InitKeys;
use app\models\crypt\InsertKey;
use app\models\crypt\Tokenizer;
use app\models\crypt\UserKeyLk;
use app\models\payonline\Cards;
use Yii;
use yii\filters\AccessControl;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\Response;

class CardkeyController extends Controller
{
    /**
     * @param $action
     * @return bool
     * @throws \yii\web\BadRequestHttpException
     */
    public function beforeAction($action)
    {
        $res = UserKeyLk::CheckSesTime();
        if (!$res) {
            $this->redirect('/keymodule');
            return true;
        }

        if (UserKeyLk::NeedUpdatePw()) {
            $this->redirect('/keymodule/default/chngpassw');
            return true;
        }

        return parent::beforeAction($action);
    }

    /**
     * Раздел настройки ключей
     *
     * @return string
     */
    public function actionIndex()
    {
        if (UserKeyLk::IsAuth()) {
            return $this->render('index');
        }
        return $this->redirect('/keymodule');
    }

    /**
     * Тест наличия ключей
     *
     * @return string
     */
    public function actionTestkek()
    {
        if (UserKeyLk::IsAuth()) {
            $cardKeys = new Tokenizer();
            return $this->render('testkek', ['result' => $cardKeys->TestKek()]);
        }
        return $this->redirect('/keymodule');
    }

    /**
     * Занесение ключа 1
     *
     * @return string
     */
    public function actionInsertkek1()
    {
        if (UserKeyLk::IsAuth() && UserKeyLk::accessKey1()) {
            return $this->render('insertkek1');
        }
        return $this->redirect('/keymodule');
    }

    /**
     * Сохранение ключа 1
     *
     * @return string|array
     * @throws \yii\db\Exception
     */
    public function actionSavekek1()
    {
        if (UserKeyLk::IsAuth() && UserKeyLk::accessKey1()) {
            UserKeyLk::logAuth(UserKeyLk::Id(), 4);
            if (Yii::$app->request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                $insertKey = new InsertKey();
                $insertKey->load(Yii::$app->request->post());
                if ($insertKey->validate()) {
                    $res = $insertKey->SaveKek1();
                    return ['status' => $res];
                } else {
                    return ['status' => 0];
                }
            }
        }
        return $this->redirect('/keymodule');
    }

    /**
     * Занесение ключа 2
     *
     * @return string
     */
    public function actionInsertkek2()
    {
        if (UserKeyLk::IsAuth() && UserKeyLk::accessKey2()) {
            return $this->render('insertkek2');
        }
        return $this->redirect('/keymodule');
    }

    /**
     * Сохранение ключа 2
     *
     * @return string|array
     * @throws \yii\db\Exception
     */
    public function actionSavekek2()
    {
        if (UserKeyLk::IsAuth() && UserKeyLk::accessKey2()) {
            UserKeyLk::logAuth(UserKeyLk::Id(), 5);
            if (Yii::$app->request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                $insertKey = new InsertKey();
                $insertKey->load(Yii::$app->request->post());
                if ($insertKey->validate()) {
                    $res = $insertKey->SaveKek2();
                    return ['status' => $res];
                } else {
                    return ['status' => 0];
                }
            }
        }
        return $this->redirect('/keymodule');
    }

    /**
     * Занесение ключа 3
     *
     * @return string
     */
    public function actionInsertkek3()
    {
        if (UserKeyLk::IsAuth() && UserKeyLk::accessKey3()) {
            return $this->render('insertkek3');
        }
        return $this->redirect('/keymodule');
    }

    /**
     * Сохранение ключа 3
     *
     * @return string|array
     * @throws \yii\db\Exception
     */
    public function actionSavekek3()
    {
        if (UserKeyLk::IsAuth() && UserKeyLk::accessKey3()) {
            UserKeyLk::logAuth(UserKeyLk::Id(), 6);
            if (Yii::$app->request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                $insertKey = new InsertKey();
                $insertKey->load(Yii::$app->request->post());
                if ($insertKey->validate()) {
                    $res = $insertKey->SaveKek3();
                    return ['status' => $res];
                } else {
                    return ['status' => 0];
                }
            }
        }
        return $this->redirect('/keymodule');
    }

    /**
     * Замена ключей
     *
     * @return string
     */
    public function actionChangekeys()
    {
        if (UserKeyLk::IsAuth() && UserKeyLk::accessKeyChange()) {
            return $this->render('changekeys');
        }
        return $this->redirect('/keymodule');
    }

    /**
     * Пересохранение ключей карт
     *
     * @return array|Response
     * @throws \yii\db\Exception
     */
    public function actionDochangekeys()
    {
        if (UserKeyLk::IsAuth() && UserKeyLk::accessKeyChange()) {
            UserKeyLk::logAuth(UserKeyLk::Id(), 7);
            if (Yii::$app->request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                $changeKeys = new ChangeKeys();
                $changeKeys->load(Yii::$app->request->post());
                if ($changeKeys->validate()) {
                    $res = $changeKeys->SaveRecryptKeys();
                    return ['status' => $res];
                } else {
                    return ['status' => 0];
                }
            }
        }
        return $this->redirect('/keymodule');
    }

    /**
     * Пересохранение ключей карт
     *
     * @return array|Response
     * @throws \yii\db\Exception
     */
    public function actionReencryptkeys()
    {
        if (UserKeyLk::IsAuth() && UserKeyLk::accessKeyChange()) {
            UserKeyLk::logAuth(UserKeyLk::Id(), 7);
            if (Yii::$app->request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                $changeKeys = new ChangeKeys();
                $changeKeys->load(Yii::$app->request->post());
                if ($changeKeys->validate()) {
                    $res = $changeKeys->ReencrypKards();
                    return ['status' => $res];
                } else {
                    return ['status' => 0];
                }
            }
        }
        return $this->redirect('/keymodule');
    }

    /**
     * Инициализация новых ключей
     *
     * @return string
     */
    public function actionInitkeys()
    {
        if (UserKeyLk::IsAuth() && UserKeyLk::accessKeyChange()) {
            return $this->render('initkeys');
        }
        return $this->redirect('/keymodule');
    }

    /**
     * Инициализация новых ключей карт
     *
     * @return array|Response
     * @throws \yii\db\Exception
     */
    public function actionDoinitkeys()
    {
        if (UserKeyLk::IsAuth() && UserKeyLk::accessKeyChange()) {
            if (Yii::$app->request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                $initKeys = new InitKeys();
                $initKeys->load(Yii::$app->request->post());
                if ($initKeys->validate()) {
                    $res = $initKeys->CreateKeys();
                    return ['status' => $res];
                } else {
                    return ['status' => 0];
                }
            }
        }
        return $this->redirect('/keymodule');
    }

}