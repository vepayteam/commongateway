<?php


namespace app\services\ident\traits;


trait ErrorModelTrait
{
    public function getError()
    {
        $err = $this->firstErrors;
        $err = array_pop($err);
        return $err;
    }
}
