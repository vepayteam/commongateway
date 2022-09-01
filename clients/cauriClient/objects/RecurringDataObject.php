<?php

namespace app\clients\cauriClient\objects;

use yii\base\BaseObject;

class RecurringDataObject extends BaseObject
{
    /**
     * @var string recurring profile token
     */
    private $id;

    /**
     * @var int days between recurring payments
     */
    private $interval;

    /**
     * @var string the last date and time when repeated payments will be available for a user in the ISO 8601 format
     */
    private $endsAt;

    /**
     * @param string $id
     * @param int $interval
     * @param string $endsAt
     */
    public function __construct(string $id, int $interval, string $endsAt)
    {
        parent::__construct();

        $this->id = $id;
        $this->interval = $interval;
        $this->endsAt = $endsAt;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getInterval(): int
    {
        return $this->interval;
    }

    /**
     * @return string
     */
    public function getEndsAt(): string
    {
        return $this->endsAt;
    }
}