<?php


namespace app\models\partner\admin\structures;

/**
 * Class VyvodSystemFilterParams
 *
 * @property string $dateFrom
 * @property string $dateTo
 * @property bool $filterByStateOp
 * @property int $typeVyvod
 *
 * @package app\models\partner\admin\structures
 */
class VyvodSystemFilterParams
{
    private $dateFrom;
    private $dateTo;
    private $filterByStateOp;
    private $typeVyvod;

    /**
     * VyvodSystemFilterParams constructor.
     *
     * @param array $params
     */
    public function __construct(array $params)
    {
        foreach ($params as $key => $value) {
            if ( property_exists($this, $key) ) {
                $this->{$key} = $value;
            }
        }
    }

    /**
     * @return string
     */
    public function getDateFrom():string
    {
        return $this->dateFrom;
    }

    /**
     * @param string $dateFrom
     */
    public function setDateFrom(string $dateFrom): void
    {
        $this->dateFrom = $dateFrom;
    }

    /**
     * @return string
     */
    public function getDateTo():string
    {
        return $this->dateTo;
    }

    /**
     * @param string $dateTo
     */
    public function setDateTo(string $dateTo): void
    {
        $this->dateTo = $dateTo;
    }

    /**
     * @return bool
     */
    public function getFilterByStateOp(): bool
    {
        return $this->filterByStateOp;
    }

    /**
     * @param $filterByStateOp
     */
    public function setFilterByStateOp(bool $filterByStateOp): void
    {
        $this->filterByStateOp = $filterByStateOp;
    }

    /**
     * @return int
     */
    public function getTypeVyvod(): int
    {
        return $this->typeVyvod;
    }

    /**
     * @param mixed $typeVyvod
     */
    public function setTypeVyvod(int $typeVyvod): void
    {
        $this->typeVyvod = $typeVyvod;
    }
}
