<?php


namespace app\services\cards;

use yii\base\Behavior;
use app\models\kfapi\KfCard;
use app\models\mfo\MfoReq;

/**
 * Class KfCardService
 * @package app\services\cards
 */
class KfCardService extends Behavior
{
    /**
     * @var array
     */
    private $_data = [];

    /**
     * @return string
     */
    public static function class(): string
    {
        return get_called_class();
    }

    /**
     * @param MfoReq $mfo
     * @param string|null $scenario
     * @param string|null $errorMsg
     * @return KfCard
     */
    public function getKfCard(MfoReq $mfo, ?string $scenario, ?string $errorMsg = 'card'): KfCard
    {
        if (isset($this->_data['kf_card']) && $this->_data['kf_card'] instanceof KfCard) {
            return $this->_data['kf_card'];
        }

        $this->_data['kf_card'] = new KfCard();
        $this->_data['kf_card']->scenario = $scenario;
        $this->_data['kf_card']->load($mfo->Req(), '');
        if (!$this->_data['kf_card']->validate()) {
            Yii::warning("$errorMsg: " . $this->_data['kf_card']->GetError());
            return $this->_data['kf_card'];
        }
        return $this->_data['kf_card'];
    }
}
