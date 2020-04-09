<?php


namespace app\models\kfapi;

use yii\helpers\IpHelper;

class CheckIP
{
    public $ips;

    /**
     * CheckIP constructor.
     * @param string|array $ips
     */
    public function __construct($ips)
    {
        if (!is_array($ips)) {
            $ips = explode(",", $ips);
        }
        $this->ips = $ips;
    }

    /**
     * Проверка IP
     * @param $ip
     * @return bool
     */
    public function MatchIP($ip)
    {
        if (empty($this->ips)) {
            return true;
        }
        foreach ($this->ips as $rule) {
            if ($rule === '*' ||
                $rule === $ip ||
                (
                    $ip !== null &&
                    ($pos = strpos($rule, '*')) !== false &&
                    strncmp($ip, $rule, $pos) === 0
                ) ||
                (
                    ($pos = strpos($rule, '/')) !== false &&
                    IpHelper::inRange($ip, $rule) === true
                )
            ) {
                return true;
            }
        }

        return false;
    }

}