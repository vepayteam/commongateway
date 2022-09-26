<?php

namespace app\models\user;

use app\models\payonline\Partner;
use app\models\payonline\User;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * @property int $ID
 * @property int $IdUser
 * @property int $IdOrg
 * @property int $TransNum Default 0.
 * @property int $DateOp Default 0.
 * @property string|null $Name Max length 50.
 * @property string|null $Fam Max length 50.
 * @property string|null $Otch Max length 50.
 * @property int $BirthDay Default 0.
 * @property string|null $Inn Max length 20.
 * @property string|null $Snils Max length 50.
 * @property string|null $PaspSer Max length 10.
 * @property string|null $PaspNum Max length 10.
 * @property string|null $PaspPodr Max length 10.
 * @property int $PaspDate Default 0.
 * @property string|null $PaspVidan Max length 200.
 * @property string|null $Phone Max length 20.
 * @property string|null $PhoneCode Max length 20.
 * @property int $StateOp 0 - created, 1 - accepted, 2 - declined. Default 0.
 * @property string|null $ErrorMessag Max length 250.
 * @property string|null $Status
 * @property int|null $DateCreate Default 1600905600 - Sep 24, 2020 (???)
 *
 * @property-read User $user {@see UserIdentification::getUser()}
 * @property-read User $partner {@see UserIdentification::getPartner()
 *
 * @todo Fix trash DB table.
 */
class UserIdentification extends ActiveRecord
{
    /**
     * {@inheritDoc}
     */
    public static function tableName()
    {
        return 'user_identification';
    }

    public function getUser(): ActiveQuery
    {
        return $this->hasOne(User::class, ['ID' => 'IdUser']);
    }

    public function getPartner(): ActiveQuery
    {
        return $this->hasOne(Partner::class, ['ID' => 'IdOrg']);
    }
}