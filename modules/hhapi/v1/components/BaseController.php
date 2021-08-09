<?php

namespace app\modules\hhapi\v1\components;

use app\components\api\ApiControllerSerializer;
use app\models\kfapi\CheckIP;
use app\models\payonline\Partner;
use app\services\payment\models\PaySchet;
use yii\filters\ContentNegotiator;
use yii\helpers\ArrayHelper;
use yii\rest\Controller;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\UnauthorizedHttpException;

class BaseController extends Controller
{
    /**
     * @var Partner
     */
    protected $partner;

    /**
     * {@inheritDoc}
     */
    public function init()
    {
        parent::init();

        $this->serializer = ApiControllerSerializer::class;
    }

    /**
     * {@inheritDoc}
     */
    public function behaviors(): array
    {
        return ArrayHelper::merge(parent::behaviors(), [
            'contentNegotiator' => [
                'class' => ContentNegotiator::class,
                'formats' => [
                    'text/html' => Response::FORMAT_JSON,
                ],
            ],
        ]);
    }

    /**
     * {@inheritDoc}
     * @throws UnauthorizedHttpException
     * @throws BadRequestHttpException
     */
    public function beforeAction($action): bool
    {
        if (!parent::beforeAction($action)) {
            return false;
        }

        if (!$this->checkAccess($this->partner)) {
            throw new UnauthorizedHttpException('Доступ запрещен.');
        }

        return true;
    }

    private function checkAccess(&$partner): bool
    {
        $login = \Yii::$app->request->headers->get('X-Mfo');
        $token = \Yii::$app->request->headers->get('X-Token');

        /** @var Partner $partner */
        $partner = Partner::find()
            ->andWhere([
                'IsDeleted' => 0,
                'IsBlocked' => 0,
                'IsMfo' => 1,
                'ID' => $login,
            ])
            ->one();
        if ($partner === null) {
            return false;
        }

        // Отсутствует проверка в режиме разработчика.
        if (\Yii::$app->params['DEVMODE'] == 'Y') {
            return true;
        }

        // Проверка IP
        if (!empty($partner->IpAccesApi)) {
            if (!(new CheckIP($partner->IpAccesApi))->MatchIP(\Yii::$app->request->remoteIP)) {
                return false;
            }
        }

        // Проверка токена
        $rawBody = \Yii::$app->request->getRawBody();
        if (sha1(sha1($partner->PaaswordApi) . sha1($rawBody)) !== $token) {
            return false;
        }

        return true;
    }

    /**
     * @param $id
     * @return PaySchet
     * @throws NotFoundHttpException
     */
    protected function findPaySchet($id): PaySchet
    {
        $paySchet = PaySchet::findOne([
            'ID' => $id,
            'IdOrg' => $this->partner->ID,
        ]);
        if ($paySchet === null) {
            throw new NotFoundHttpException('Счет не найден.');
        }

        return $paySchet;
    }
}