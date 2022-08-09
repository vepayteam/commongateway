<?php

namespace app\services\payment\banks;

use app\services\payment\models\Bank;

/**
 * Common base class for adapters.
 */
abstract class BaseAdapter implements IBankAdapter
{
    /**
     * @var Bank
     */
    private $bankModel;

    /**
     * Returns cached clean bank model.
     *
     * @return Bank
     */
    public function getBankModel(): Bank
    {
        if ($this->bankModel === null) {
            $this->bankModel = Bank::findOne($this::bankId());
        }

        // refresh if model's data changed
        if ($this->bankModel->getDirtyAttributes() !== [] || $this->bankModel->getRelatedRecords() !== []) {
            $this->bankModel->refresh();
        }

        return $this->bankModel;
    }

    /**
     * {@inheritDoc}
     */
    public function getOutCardRefreshStatusDelay(): int
    {
        return $this->getBankModel()->OutCardRefreshStatusDelay;
    }
}