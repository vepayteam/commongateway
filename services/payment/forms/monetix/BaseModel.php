<?php

namespace app\services\payment\forms\monetix;

use app\models\traits\ValidateFormTrait;
use RecursiveArrayIterator;
use RecursiveIteratorIterator;

abstract class BaseModel extends \yii\base\Model implements \JsonSerializable
{
    use ValidateFormTrait;

    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        $result = [];
        foreach ($this->attributes() as $attribute) {
            if ($this->$attribute && is_object($this->$attribute) && $this->$attribute instanceof BaseModel) {
                $result[$attribute] = $this->$attribute->jsonSerialize();
            } elseif ($this->$attribute && !is_object($this->$attribute)) {
                $result[$attribute] = $this->$attribute;
            }
        }
        return $result;
    }

    public function buildSignature(string $key): string
    {
        $data = implode(";", $this->getFieldsBySignature());
        $hashBinary = hash_hmac('sha512', $data, $key, true);
        $hashBase64 = base64_encode($hashBinary);
        return $hashBase64;
    }

    private function getFieldsBySignature(): array
    {
        $result = [];
        $iterator = new RecursiveIteratorIterator(new RecursiveArrayIterator($this->jsonSerialize()));
        foreach ($iterator as $key => $value) {
            $keys = array();
            $keys[] = $key;
            for ($i = $iterator->getDepth() - 1; $i >= 0; $i--) {
                $keys[] = $iterator->getSubIterator($i)->key();
            }
            $key_paths = array_reverse($keys);
            $result[] = implode(':', $key_paths) . ':' . utf8_encode($value);
        }
        sort($result);
        return $result;
    }
}