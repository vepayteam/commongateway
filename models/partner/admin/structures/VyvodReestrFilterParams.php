<?php


namespace app\models\partner\admin\structures;

/**
 * Class VyvodSystemFilterParams
 *
 * @property string $dateFrom
 * @property string $dateTo
 * @property bool $filterByStateOp
 * @property int $typeVyvyod
 *
 * @package app\models\partner\admin\structures
 */
class VyvodReestrFilterParams
{
    private $dateFrom;
    private $dateTo;
    private $filterByStateOp;
    private $typeVyvyod;

    /**
     * VyvodSystemFilterParams constructor.
     *
     * @param array $middleMapResult
     */
    public function __construct(array $middleMapResult)
    {
        foreach ($middleMapResult as $key => $value) {
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
    public function getTypeVyvyod(): int
    {
        return $this->typeVyvyod;
    }

    /**
     * @param mixed $typeVyvyod
     */
    public function setTypeVyvyod(int $typeVyvyod): void
    {
        $this->typeVyvyod = $typeVyvyod;
    }
}
