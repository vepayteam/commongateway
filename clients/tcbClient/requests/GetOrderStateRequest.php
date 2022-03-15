<?php

namespace app\clients\tcbClient\requests;

use yii\base\BaseObject;

/**
 * @property-read string $extId
 */
class GetOrderStateRequest extends BaseObject
{
    private $_extId;

    /**
     * @param string $extId
     */
    public function __construct(string $extId)
    {
        parent::__construct();

        $this->_extId = $extId;
    }

    /**
     * @return string
     */
    public function getExtId(): string
    {
        return $this->_extId;
    }
}