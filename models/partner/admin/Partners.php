<?php

namespace app\models\partner\admin;

use app\models\payonline\Partner;
use app\models\payonline\PartnerBankRekviz;
use yii\base\DynamicModel;

class Partners
{
    public $loadError;
    public $loadErrorMesg;

    public function getPartnersList()
    {
        $part = Partner::find()->with('partner_bank_rekviz')->where(['IsDeleted' => 0]);
        return $part->all();
    }

    public function getRekvizList($IdPartner)
    {
        $rekviz = PartnerBankRekviz::find()->with('partner')->where(['IsDeleted' => 0]);
        if ($IdPartner > 0) {
            $rekviz->andWhere(['IdPartner' => $IdPartner]);
        }
        return $rekviz->all();
    }

    /**
     * @param \yii\web\Request $request
     * @throws \yii\base\InvalidConfigException
     */
    public function updateContPartner($request)
    {
        $partner = Partner::getPartner($request->post('IdPartner', 0));
        if (!$partner) {
            $this->loadError = true;
            $this->loadErrorMesg = ['Не найден'];
        }
        $model = DynamicModel::validateData([
            'KontTehEmail' => $request->post('KontTehEmail', ''),
            'KontTehPhone' => $request->post('KontTehPhone', ''),
            'KontFinansEmail' => $request->post('KontFinansEmail', ''),
            'KontFinansPhone' => $request->post('KontFinansPhone', ''),
            'KontTehFio' => $request->post('KontTehFio', ''),
            'KontFinansFio' => $request->post('KontFinansFio', ''),
            'URLSite' => $request->post('URLSite', ''),
            'Phone' => $request->post('Phone', ''),
            'Email' => $request->post('Email', '')
        ], [
            [['KontTehFio', 'KontFinansFio', 'URLSite'], 'string', 'max' => 100],
            [['KontTehEmail', 'KontTehPhone', 'KontFinansEmail', 'KontFinansPhone',
                'Phone', 'Email'], 'string', 'max' => 50]
        ]);

        if ($model->hasErrors()) {
            $this->loadError = true;
            $this->loadErrorMesg = array_values($model->getFirstErrors());
        } else {
            $this->loadError = false;
            $partner->setAttributes($model->toArray());
            $partner->save(false);
        }
    }
}
