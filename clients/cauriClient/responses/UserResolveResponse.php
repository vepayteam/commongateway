<?php

namespace app\clients\cauriClient\responses;

use yii\base\BaseObject;

class UserResolveResponse extends BaseObject
{
    /**
     * @var int gate user id
     */
    private $id;

    /**
     * @param int $id
     */
    public function __construct(int $id)
    {
        parent::__construct();

        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }
}