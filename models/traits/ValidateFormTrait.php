<?php


namespace app\models\traits;


trait ValidateFormTrait
{
    public function GetError()
    {
        $err = $this->firstErrors;
        $err = array_pop($err);
        return $err;
    }
}
