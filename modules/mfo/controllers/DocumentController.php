<?php

namespace app\modules\mfo\controllers;

use app\models\mfo\MfoReq;
use app\models\payonline\Partner;
use app\modules\mfo\components\BaseApiController;
use app\services\payment\models\PaySchet;
use kartik\mpdf\Pdf;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class DocumentController extends BaseApiController
{
    /**
     * Outputs confirmation PDF document.
     *
     * @param string|int $id
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionConfirmation($id)
    {
        $mfo = new MfoReq();
        $mfo->LoadData(\Yii::$app->request->getRawBody());
        $partner = $mfo->getPartner();

        $paySchet = $this->findPaySchet($partner, $id);

        $content = $this->renderPartial('confirmation', [
            'partner' => $partner,
            'paySchet' => $paySchet,
        ]);

        $pdf = new Pdf([
            'mode' => Pdf::MODE_UTF8,
            'content' => $content,
            'cssFile' => null,
            'destination' => Pdf::DEST_DOWNLOAD,
            'filename' => "confirm_{$paySchet->ID}.pdf",
        ]);

        \Yii::$app->response->format = Response::FORMAT_RAW;
        return $pdf->render();
    }

    private function findPaySchet(Partner $partner, $id): PaySchet
    {
        $paySchet = PaySchet::findOne([
            'IdOrg' => $partner->ID,
            'ID' => $id,
        ]);
        if ($paySchet === null) {
            throw new NotFoundHttpException('Платеж не найден.');
        }

        return $paySchet;
    }
}