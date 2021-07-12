<?php

namespace app\models\partner\admin;

use app\models\payonline\Partner;
use app\models\payonline\Uslugatovar;
use app\services\PartnerService;
use Yii;
use yii\base\DynamicModel;
use yii\helpers\FileHelper;

class Uslugi
{
    public $loadError = true;
    public $loadErrorMesg = '';
    public $loadID = 0;

    /**
     * Список услуг
     * @param int $IdPart
     * @return Uslugatovar[]|array|\yii\db\ActiveRecord[]
     */
    public function getList($IdPart)
    {
        $usl = Uslugatovar::find()
            ->with('qr_group', 'uslugi_regions')
            ->where(['IsDeleted' => 0]);//, 'IsCustom' => 0
        if ($IdPart > 0) {
            $usl->andWhere(['IDPartner' => $IdPart]);
        }

        return $usl->all();
    }

    /**
     * Список точек
     * @param int $IdPart
     * @return Uslugatovar[]|array|\yii\db\ActiveRecord[]
     */
    public function getPointsList($IdPart)
    {
        $usl = Uslugatovar::find()
            ->with('qr_group', 'uslugi_regions')
            ->where(['IsDeleted' => 0])
            ;//->andWhere('IsCustom > 0');
        if ($IdPart > 0) {
            $usl->andWhere(['IDPartner' => $IdPart]);
        }

        return $usl->all();
    }

    /**
     * @param yii\web\Request $request
     * @throws \yii\base\Exception
     * @throws \yii\base\InvalidConfigException
     */
    public function updateUsluga($request)
    {
        /** @var PartnerService $partnerService */
        $partnerService = \Yii::$app->get(PartnerService::class);

        $partner = $partnerService->getPartner($request->post('idpart', 0));
        $usl = Uslugatovar::findOne(['ID' => $request->post('ID'), 'IDPartner' => $partner->ID]);
        if (!$usl) {
            $usl = new Uslugatovar();
            $usl->IDPartner = $partner->ID;
        }
        $model = DynamicModel::validateData([
            'IdMagazin' => $request->post('IdMagazin', 0),
            'EmailShablon' => $request->post('EmailShablon', ''),
            'NameUsluga' => $request->post('NameUsluga', ''),
            'EnabledStatus' => $request->post('EnabledStatus', 0),
            'SitePoint' => $request->post('SitePoint', ''),
            'EmailReestr' => $request->post('EmailReestr', ''),
            'UrlInform' => $request->post('UrlInform', ''),
            'UrlReturn' => $request->post('UrlReturn', ''),
            'ColorWdtMain' => $request->post('ColorWdtMain', ''),
            'ColorWdtActive' => $request->post('ColorWdtActive', '')
        ], [
            [['NameUsluga'], 'string', 'max' => 200],
            [['EnabledStatus', 'IdMagazin'], 'integer'],
            [['SitePoint', 'EmailReestr'], 'string', 'max' => 50],
            [['ColorWdtMain', 'ColorWdtActive'], 'string', 'max' => 10],
            ['EmailReestr', 'email'],
            [['EmailShablon'], 'string', 'max' => 2000]
        ]);

        if ($model->hasErrors()) {
            $this->loadError = true;
            $this->loadErrorMesg = array_values($model->getFirstErrors());
        } else {
            $this->loadError = false;
            $usl->setAttributes($model->toArray());
            $usl->save(false);

            $this->loadID = $usl->ID;

            $fileLogoProv = \yii\web\UploadedFile::getInstanceByName('fileLogoProv');
            if ($fileLogoProv) {
                $usl->updateAttributes(['LogoProv' => md5($usl->NameUsluga).".png"]);
                $this->uploadLogo($fileLogoProv, md5($usl->NameUsluga), $usl->IDPartner);
                unlink($fileLogoProv->tempName);
            }

        }
    }

    /**
     * @param yii\web\UploadedFile $fileLogoProv
     * @param string $uid
     * @param int $IdPartner
     * @throws \yii\base\Exception
     */
    protected function uploadLogo($fileLogoProv, $uid, $IdPartner)
    {
        $allowType = ['image/png', 'image/jpeg'];
        $allowExt = ['png', 'jpg', 'jpeg'];
        if (!in_array($fileLogoProv->extension, $allowExt)) {
            return;
        }
        if (!in_array($fileLogoProv->type, $allowType)) {
            return;
        }
        $item = file_get_contents($fileLogoProv->tempName);

        $this->uploadImg($item, $uid, $IdPartner, 100, 35);
    }

    /**
     * @param string $imgstr
     * @param string $uid
     * @param int $IdPartner
     * @throws \yii\base\Exception
     */
    public function uploadWidgetImg($imgstr, $uid, $IdPartner)
    {
        if (!preg_match('/data:image\/([^;]*);base64,(.*)/', $imgstr, $matches)) {
            return;
        }
        $content = base64_decode($matches[2]);

        $this->uploadImg($content, $uid, $IdPartner, 240, 240);
    }

    /**
     * @param string $content
     * @param string $uid
     * @param int $IdPartner
     * @param int $maxW
     * @param int $maxH
     * @throws \yii\base\Exception
     */
    protected function uploadImg($content, $uid, $IdPartner, $maxW, $maxH)
    {
        $file = 'shopdata/'.$IdPartner;
        if (!file_exists($file)) {
            FileHelper::createDirectory($file);
        }
        $file .= '/'.$uid.'.png';
        $gd = imagecreatefromstring($content);
        $prop = getimagesizefromstring($content);
        $sW = $prop[0];
        $sH = $prop[1];
        $dW = $maxW;
        $dH = $maxH;
        if ($sW > $maxW) {
            $dW = round($dW * $sW / $sH);
            $dH = round($dH * $sW / $sH);
        } elseif ($sH > $maxH) {
            $dW = round($dW * $sH / $sW);
            $dH = round($dH * $sH / $sW);
        }
        $tmpImg = imagecreatetruecolor($dW, $dH);
        imagealphablending($tmpImg, false);
        imagesavealpha($tmpImg, true);
        imagecopyresampled($tmpImg, $gd, 0, 0, 0, 0, $dW,
            $dH, $sW, $sH);
        imagepng($tmpImg, $file);
    }

}
