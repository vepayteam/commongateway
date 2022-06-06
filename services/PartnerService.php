<?php

namespace app\services;

use app\models\partner\admin\structures\VyvodSystemFilterParams;
use app\models\partner\admin\VoznagStatNew;
use app\models\partner\UserLk;
use app\models\payonline\Partner;
use app\models\payonline\Uslugatovar;
use app\models\queue\SendMailJob;
use app\models\site\PartnerReg;
use app\models\TU;
use yii\base\Component;
use yii\web\UploadedFile;

/**
 * Сервис для сущности Партнёр.
 */
class PartnerService extends Component
{

    /**
     * Самостоятельная регистрация Партнёра.
     *
     * @param Partner $partner
     * @param PartnerReg $partnerReg
     * @throws \yii\db\Exception
     */
    public function register(Partner $partner, PartnerReg $partnerReg)
    {
        if (!$partner->isNewRecord) {
            throw new \Exception('Only new record allowed.');
        }

        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $this->createPartner($partner);

            $partnerReg->State = PartnerReg::STATE_REGISTERED;
            $partnerReg->save(false);

            $this->createUslugatovars($partner);

            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }

        \Yii::$app->queue->push(
            new SendMailJob([
                'email' => 'info@vepay.online',
                'subject' => "Зарегистрирован контрагент",
                'content' => "Зарегистрирован контрагент {$partner->Name}",
            ])
        );
    }

    /**
     * Создание Партнёра.
     *
     * @param Partner $partner
     * @throws \yii\db\Exception
     */
    public function create(Partner $partner)
    {
        if (!$partner->isNewRecord) {
            throw new \Exception('Only new record allowed.');
        }

        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $this->createPartner($partner);

            $this->createUslugatovars($partner);

            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    protected function createPartner(Partner $partner)
    {
        $partner->DateRegister = time();
        $partner->save(false);
    }

    /**
     * Добавление услуг при создании партнера.
     *
     * @todo Легаси. Назвать метод, когда "Uslugatovar" будет переименован.
     */
    protected function createUslugatovars(Partner $partner)
    {
        if ($partner->IsMfo) {
            // создание услуг МФО при добавлении
            $usluga = new Uslugatovar();
            $usluga->IDPartner = $partner->ID;
            $usluga->IsCustom = TU::$TOSCHET;
            $usluga->NameUsluga = "Выдача займа на счет.{$partner->Name}";
            $usluga->PcComission = 0;
            $usluga->TypeExport = 1;
            $usluga->ProvComisPC = 0.2;
            $usluga->ProvComisMin = 25;
            $usluga->ProvVoznagPC = 0.4;
            $usluga->ProvVoznagMin = 35;
            $usluga->save(false);

            $usluga = new Uslugatovar();
            $usluga->IDPartner = $partner->ID;
            $usluga->IsCustom = TU::$TOCARD;
            $usluga->NameUsluga = "Выдача займа на карту.{$partner->Name}";
            $usluga->PcComission = 0;
            $usluga->TypeExport = 1;
            $usluga->ProvComisPC = 0.25;
            $usluga->ProvComisMin = 25;
            $usluga->ProvVoznagPC = 0.5;
            $usluga->ProvVoznagMin = 45;
            $usluga->save(false);

            $usluga = new Uslugatovar();
            $usluga->IDPartner = $partner->ID;
            $usluga->IsCustom = TU::$POGASHATF;
            $usluga->NameUsluga = "Погашение займа AFT.{$partner->Name}";
            $usluga->PcComission = 2.2;
            $usluga->MinsumComiss = 0.01;
            $usluga->ProvComisPC = 0.5;
            $usluga->ProvComisMin = 25;
            $usluga->TypeExport = 1;
            $usluga->save(false);

            $usluga = new Uslugatovar();
            $usluga->IDPartner = $partner->ID;
            $usluga->IsCustom = TU::$POGASHECOM;
            $usluga->NameUsluga = "Погашение займа ECOM.{$partner->Name}";
            $usluga->PcComission = 2.2;
            $usluga->MinsumComiss = 0.01;
            $usluga->ProvComisPC = 1.85;
            $usluga->ProvComisMin = 0;
            $usluga->TypeExport = 1;
            $usluga->save(false);

            $usluga = new Uslugatovar();
            $usluga->IDPartner = $partner->ID;
            $usluga->IsCustom = TU::$AVTOPLATECOM;
            $usluga->NameUsluga = "Автоплатеж по займу ECOM.{$partner->Name}";
            $usluga->PcComission = 2.2;
            $usluga->MinsumComiss = 0.60;
            $usluga->ProvComisPC = 2;
            $usluga->ProvComisMin = 0.60;
            $usluga->TypeExport = 1;
            $usluga->save(false);

            $usluga = new Uslugatovar();
            $usluga->IDPartner = 1;
            $usluga->ExtReestrIDUsluga = $partner->ID;
            $usluga->IsCustom = TU::$VYPLATVOZN;
            $usluga->NameUsluga = "Комиссия.{$partner->Name}";
            $usluga->PcComission = 0;
            $usluga->ProvComisPC = 0;
            $usluga->ProvComisMin = 25;
            $usluga->TypeExport = 1;
            $usluga->save(false);

            $usluga = new Uslugatovar();
            $usluga->IDPartner = 1;
            $usluga->ExtReestrIDUsluga = $partner->ID;
            $usluga->IsCustom = TU::$VYVODPAYS;
            $usluga->NameUsluga = "Перечисление.{$partner->Name}";
            $usluga->PcComission = 0;
            $usluga->ProvComisPC = 0;
            $usluga->ProvComisMin = 25;
            $usluga->TypeExport = 1;
            $usluga->save(false);

            $usluga = new Uslugatovar();
            $usluga->IDPartner = 1;
            $usluga->ExtReestrIDUsluga = $partner->ID;
            $usluga->IsCustom = TU::$REVERSCOMIS;
            $usluga->NameUsluga = "Возмещение комиссии.{$partner->Name}";
            $usluga->PcComission = 0;
            $usluga->ProvComisPC = 0;
            $usluga->ProvComisMin = 0;
            $usluga->TypeExport = 1;
            $usluga->save(false);

            $usluga = new Uslugatovar();
            $usluga->IDPartner = 1;
            $usluga->ExtReestrIDUsluga = $partner->ID;
            $usluga->IsCustom = TU::$PEREVPAYS;
            $usluga->NameUsluga = "Перечисление на выдачу.{$partner->Name}";
            $usluga->PcComission = 0;
            $usluga->ProvComisPC = 0;
            $usluga->ProvComisMin = 0;
            $usluga->TypeExport = 1;
            $usluga->save(false);
        } else {
            // создание услуг магазину при добавлении
            $usluga = new Uslugatovar();
            $usluga->IDPartner = $partner->ID;
            $usluga->IsCustom = TU::$ECOM;
            $usluga->NameUsluga = "Оплата.{$partner->Name}";
            $usluga->PcComission = 2.2;
            $usluga->ProvComisPC = 1.85;
            $usluga->ProvComisMin = 0;
            $usluga->TypeExport = 1;
            $usluga->save(false);

            $usluga = new Uslugatovar();
            $usluga->IDPartner = $partner->ID;
            $usluga->IsCustom = TU::$AVTOPLATECOM;
            $usluga->NameUsluga = "Автоплатеж.{$partner->Name}";
            $usluga->PcComission = 2.2;
            $usluga->ProvComisPC = 2.0;
            $usluga->ProvComisMin = 0.60;
            $usluga->TypeExport = 1;
            $usluga->save(false);

            $usluga = new Uslugatovar();
            $usluga->IDPartner = 1;
            $usluga->ExtReestrIDUsluga = $partner->ID;
            $usluga->IsCustom = TU::$VYVODPAYS;
            $usluga->NameUsluga = "Перечисление.{$partner->Name}";
            $usluga->PcComission = 0;
            $usluga->ProvComisPC = 0;
            $usluga->ProvComisMin = 25;
            $usluga->TypeExport = 1;
            $usluga->save(false);
        }
    }

    /**
     * @param $id
     * @return null|Partner
     * @todo Легаси. Удалить.
     */
    public function getPartner($id)
    {
        if (!UserLk::IsAdmin(\Yii::$app->user)) {
            $id = UserLk::getPartnerId(\Yii::$app->user);
        }

        return Partner::findOne($id);
    }

    /**
     * Сохраняет загруженные файлы ключей ККМ.
     *
     * @param Partner $partner
     * @param UploadedFile|null $uploadedSingKey
     * @param UploadedFile|null $uploadedConKey
     * @param UploadedFile|null $uploadedConCert
     * @return bool
     * @todo Легаси. Оптимизировать алгоритм: убрать повторения, добавить универсальный метод сохранения файлов ключей.
     */
    public function saveKeysKkm(
        Partner $partner,
        ?UploadedFile $uploadedSingKey,
        ?UploadedFile $uploadedConKey,
        ?UploadedFile $uploadedConCert
    ): bool
    {
        $partner->OrangeDataSingKey = $uploadedSingKey;
        $partner->OrangeDataConKey = $uploadedConKey;
        $partner->OrangeDataConCert = $uploadedConCert;
        if (!$partner->validate()) {
            return false;
        }
        $isSingKeySaved = $isConKeySaved = $isConCertSaved = true;
        $path = \Yii::$app->basePath . '/config/kassaclients/';
        if (!file_exists($path)) {
            if (!mkdir($path) && !is_dir($path)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $path));
            }
        }
        if ($uploadedSingKey) {
            $oldFileName = $path . $partner->oldAttributes['OrangeDataSingKey'];
            if (file_exists($oldFileName)) {
                @unlink($oldFileName);
            }
            $fileBaseName = "{$partner->ID}_{$uploadedSingKey->baseName}.{$uploadedSingKey->extension}";
            $isSingKeySaved = $uploadedSingKey->saveAs($path . $fileBaseName);
            $partner->OrangeDataSingKey = $fileBaseName;
        } else {
            $partner->OrangeDataSingKey = $partner->oldAttributes['OrangeDataSingKey'];
        }

        if ($uploadedConKey) {
            $oldFileName = $path . $partner->oldAttributes['OrangeDataConKey'];
            if (file_exists($oldFileName)) {
                @unlink($oldFileName);
            }
            $fileBaseName = "{$partner->ID}_{$uploadedConKey->baseName}.{$uploadedConKey->extension}";
            $isConKeySaved = $uploadedConKey->saveAs($path . $fileBaseName);
            $partner->OrangeDataConKey = $fileBaseName;
        } else {
            $partner->OrangeDataConKey = $partner->oldAttributes['OrangeDataConKey'];
        }

        if ($uploadedConCert) {
            $oldFileName = $path . $partner->oldAttributes['OrangeDataConCert'];
            if (file_exists($oldFileName)) {
                @unlink($oldFileName);
            }
            $fileBaseName = "{$partner->ID}_{$uploadedConCert->baseName}.{$uploadedConCert->extension}";
            $isConCertSaved = $uploadedConCert->saveAs($path . $fileBaseName);
            $partner->OrangeDataConCert = $fileBaseName;
        } else {
            $partner->OrangeDataConCert = $partner->oldAttributes['OrangeDataConCert'];
        }

        $partner->save(false);

        return $isSingKeySaved && $isConKeySaved && $isConCertSaved;
    }

    /**
     * Сохраняет загруженные файлы ключей Apple Pay.
     *
     * @param Partner $partner
     * @param UploadedFile|null $uploadedKey
     * @param UploadedFile|null $uploadedCert
     * @return bool
     * @todo Легаси. Оптимизировать алгоритм: убрать повторения, добавить универсальный метод сохранения файлов ключей.
     */
    public function saveKeysApplepay(Partner $partner, ?UploadedFile $uploadedKey, ?UploadedFile $uploadedCert): bool
    {
        $partner->Apple_MerchIdentKey = $uploadedKey;
        $partner->Apple_MerchIdentCert = $uploadedCert;
        if (!$partner->validate()) {
            return false;
        }
        $isKeySaved = $isCertSaved = true;
        $path = \Yii::$app->basePath . '/config/applepayclients/';
        if (!file_exists($path)) {
            if (!mkdir($path) && !is_dir($path)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $path));
            }
        }
        if ($uploadedKey) {
            $oldFileName = $path . $partner->oldAttributes['Apple_MerchIdentKey'];
            if (file_exists($oldFileName)) {
                @unlink($oldFileName);
            }
            $fileBaseName = "{$partner->ID}_{$uploadedKey->baseName}.{$uploadedKey->extension}";
            $isKeySaved = $uploadedKey->saveAs($path . $fileBaseName);
            $partner->Apple_MerchIdentKey = $fileBaseName;
        } else {
            $partner->Apple_MerchIdentKey = $partner->oldAttributes['Apple_MerchIdentKey'];
        }

        if ($uploadedCert) {
            $oldFileName = $path . $partner->oldAttributes['Apple_MerchIdentCert'];
            if (file_exists($oldFileName)) {
                @unlink($oldFileName);
            }
            $fileBaseName = "{$partner->ID}_{$uploadedCert->baseName}.{$uploadedCert->extension}";
            $isCertSaved = $uploadedCert->saveAs($path . $fileBaseName);
            $partner->Apple_MerchIdentCert = $fileBaseName;
        } else {
            $partner->Apple_MerchIdentCert = $partner->oldAttributes['Apple_MerchIdentCert'];
        }

        $partner->save(false);

        return $isKeySaved && $isCertSaved;
    }

    /**
     * @param Partner $partner
     * @param VyvodSystemFilterParams $params
     * @return false|string|null
     * @todo Легаси. Придумать нормальное название.
     */
    public function getSummVyveden(Partner $partner, VyvodSystemFilterParams $params)
    {
        $query = $partner->getVyvodSystem()
            ->select(['SUM(`Summ`)'])
            ->andWhere([
                'or',
                ['and', ['>=', 'DateFrom', $params->getDateFrom()], ['<=', 'DateTo', $params->getDateTo()]],
                ['between', 'DateFrom', $params->getDateFrom(), $params->getDateTo()],
                ['between', 'DateTo', $params->getDateFrom(), $params->getDateTo()],
            ])
            ->andWhere(['TypeVyvod' => $params->getTypeVyvod()])
            ->cache(60 * 60);

        if ($params->getFilterByStateOp() === true) {
            $query->andWhere(['SatateOp' => [VoznagStatNew::OPERATION_STATE_IN_PROGRESS, VoznagStatNew::OPERATION_STATE_READY]]);
        }

        return $query->scalar();
    }


    /**
     * @param Partner $partner
     * @param VyvodSystemFilterParams $params
     * @return false|string|null
     * @todo Легаси. Придумать нормальное название.
     */
    public function getDataVyveden(Partner $partner, VyvodSystemFilterParams $params)
    {
        $query = $partner->getVyvodSystem()
            ->select(['DateTo'])
            ->andWhere(['<=', 'DateTo', $params->getDateTo()])
            ->andWhere(['TypeVyvod' => $params->getTypeVyvod()])
            ->orderBy(['DateTo' => SORT_DESC])
            ->cache(60 * 60);

        if ($params->getFilterByStateOp() === true) {
            $query->andWhere(['SatateOp' => [VoznagStatNew::OPERATION_STATE_IN_PROGRESS, VoznagStatNew::OPERATION_STATE_READY]]);
        }

        return $query->scalar();
    }

    /**
     * @param Partner $partner
     * @param VyvodSystemFilterParams $params
     * @return false|string|null
     * @todo Легаси. Придумать нормальное название.
     */
    public function getSummPerechisl(Partner $partner, VyvodSystemFilterParams $params)
    {
        $query = $partner->getVyvodReestr()
            ->select(['SUM(`SumOp`)'])
            ->andWhere([
                'or',
                ['and', ['>=', 'DateFrom', $params->getDateFrom()], ['<=', 'DateTo', $params->getDateTo()]],
                ['between', 'DateFrom', $params->getDateFrom(), $params->getDateTo()],
                ['between', 'DateTo', $params->getDateFrom(), $params->getDateTo()],
            ])
            ->cache(60 * 60);

        if ($params->getFilterByStateOp() === true) {
            $query->andWhere(['StateOp' => [VoznagStatNew::OPERATION_STATE_IN_PROGRESS, VoznagStatNew::OPERATION_STATE_READY]]);
        }

        return $query->scalar();
    }

    /**
     * @param Partner $partner
     * @param VyvodSystemFilterParams $params
     * @return false|string|null
     * @todo Легаси. Придумать нормальное название.
     */
    public function getDataPerechisl(Partner $partner, VyvodSystemFilterParams $params)
    {
        $query = $partner->getVyvodReestr()
            ->select(['DateTo'])
            ->andWhere(['<=', 'DateTo', $params->getDateTo()])
            ->orderBy(['DateTo' => SORT_DESC])
            ->cache(60 * 60);

        if ($params->getFilterByStateOp() === true) {
            $query->andWhere(['StateOp' => [VoznagStatNew::OPERATION_STATE_IN_PROGRESS, VoznagStatNew::OPERATION_STATE_READY]]);
        }

        return $query->scalar();
    }

}