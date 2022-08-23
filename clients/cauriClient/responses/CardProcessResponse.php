<?php

namespace app\clients\cauriClient\responses;

use app\clients\cauriClient\objects\AcsDataObject;
use app\clients\cauriClient\objects\CardDataObject;
use app\clients\cauriClient\objects\RecurringDataObject;
use yii\base\BaseObject;

class CardProcessResponse extends BaseObject
{
    /**
     * @var int identifier of a transaction
     */
    private $id;

    /**
     * @var bool indicates whether request has been accepted for processing
     */
    private $success;

    /**
     * @var string|null response code of a transaction. Required only when success is false.
     */
    private $responseCode;

    /**
     * @var CardDataObject
     */
    private $card;

    /**
     * @var AcsDataObject|null
     */
    private $acs;

    /**
     * @var RecurringDataObject|null
     */
    private $recurring;

    /**
     * @param int $id
     * @param bool $success
     * @param string|null $responseCode
     * @param CardDataObject $card
     * @param AcsDataObject|null $acs
     * @param RecurringDataObject|null $recurring
     */
    public function __construct(
        int                  $id,
        bool                 $success,
        ?string              $responseCode,
        CardDataObject       $card,
        ?AcsDataObject       $acs,
        ?RecurringDataObject $recurring
    )
    {
        parent::__construct();

        $this->id = $id;
        $this->success = $success;
        $this->responseCode = $responseCode;
        $this->card = $card;
        $this->acs = $acs;
        $this->recurring = $recurring;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return bool
     */
    public function isSuccess(): bool
    {
        return $this->success;
    }

    /**
     * @return string|null
     */
    public function getResponseCode(): ?string
    {
        return $this->responseCode;
    }

    /**
     * @return CardDataObject
     */
    public function getCard(): CardDataObject
    {
        return $this->card;
    }

    /**
     * @return AcsDataObject|null
     */
    public function getAcs(): ?AcsDataObject
    {
        return $this->acs;
    }

    /**
     * @return RecurringDataObject|null
     */
    public function getRecurring(): ?RecurringDataObject
    {
        return $this->recurring;
    }
}