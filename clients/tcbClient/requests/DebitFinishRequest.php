<?php

namespace app\clients\tcbClient\requests;

use yii\base\BaseObject;

/**
 * @property-read string $extId
 * @property-read string $md
 * @property-read string $paRes
 */
class DebitFinishRequest extends BaseObject
{
    /**
     * @var string
     */
    private $_extId;
    /**
     * @var string
     */
    private $_md;
    /**
     * @var string
     */
    private $_paRes;

    /**
     * @param string $extId Уникальный идентификатор операции.
     * @param string $md Параметр, указанный в запросе на прохождение в ACS.
     * @param string $paRes Параметр, полученный в ответе от метода finish на ACS.
     */
    public function __construct(string $extId, string $md, string $paRes)
    {
        parent::__construct();

        $this->_extId = $extId;
        $this->_md = $md;
        $this->_paRes = $paRes;
    }

    /**
     * @return string
     */
    public function getExtId(): string
    {
        return $this->_extId;
    }

    /**
     * @return string
     */
    public function getMd(): string
    {
        return $this->_md;
    }

    /**
     * @return string
     */
    public function getPaRes(): string
    {
        return $this->_paRes;
    }
}