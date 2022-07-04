<?php

namespace app\clients\payloniumClient\requests;

use yii\base\BaseObject;

class BaseRequest extends BaseObject
{
    /**
     * @return string
     */
    public function toRequestString(): string
    {
        return $this->convertXmlElementToString($this->getCommonRequestXmlElement());
    }

    /**
     * @return \SimpleXMLElement
     */
    protected function getCommonRequestXmlElement(): \SimpleXMLElement
    {
        return new \SimpleXMLElement('<request></request>');
    }

    /**
     * Функция конвертации \SimpleXMLElement в строку
     * Если использовать встроенную функцию $xml->saveXML(), то добавится ненужная шапка <?xml version="1.0"?>
     *
     * @param \SimpleXMLElement $xml
     * @return string
     */
    protected function convertXmlElementToString(\SimpleXMLElement $xml): string
    {
        $dom = dom_import_simplexml($xml);
        return $dom->ownerDocument->saveXML($dom->ownerDocument->documentElement);
    }
}