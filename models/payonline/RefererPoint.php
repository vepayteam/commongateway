<?php

namespace app\models\payonline;

class RefererPoint
{
    public function getAgentBySite($url)
    {
        if (!empty($url)) {
            $usl = Uslugatovar::find()
                ->where('SitePoint LIKE :URL', [':URL' => '%'.$url])
                ->one();
            if ($usl) {
                return $usl->IDPartner;
            }
        }
        return 0;
    }
}