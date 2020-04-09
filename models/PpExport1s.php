<?php

namespace app\models;

class PpExport1s
{
    public $recviz;
    public $datepp;
    private $pp = [];

    public function __construct($recviz)
    {
        $this->recviz = $recviz;
        $this->datepp = time();
    }

    /**
     * Платежное поручение
     * @param $pp
     */
    public function AddPp($pp)
    {
        $this->pp[] = $pp;
    }

    /**
     * Текст выгрузки
     * @return string
     */
    public function Export()
    {
        return $this->header().$this->content().$this->footer();
    }

    private function header()
    {
        //1C - header
        $DateCreate = date("d.m.Y");
        $TimeCreate = date("H:i:s");
        $DateBegin = date("d.m.Y", $this->datepp);
        $DateEnd = date("d.m.Y", $this->datepp);

        $reestrtext  = "1CClientBankExchange\r\n";
        $reestrtext .= "ВерсияФормата=1.02\r\n";
        $reestrtext .= "Кодировка=Windows\r\n";
        $reestrtext .= "Отправитель=1C\r\n";
        $reestrtext .= "Получатель=\r\n";
        $reestrtext .= "ДатаСоздания=".$DateCreate."\r\n";
        $reestrtext .= "ВремяСоздания=".$TimeCreate."\r\n";
        $reestrtext .= "ДатаНачала=".$DateBegin."\r\n";
        $reestrtext .= "ДатаКонца=".$DateEnd."\r\n";
        $reestrtext .= "РасчСчет=".$this->recviz->RaschShet."\r\n";
        return $reestrtext;
    }

    private function content()
    {
        $reestrtext = "";
        foreach ($this->pp as $pp) {
            $reestrtext .= "СекцияДокумент=Платежное поручение\r\n";
            $reestrtext .= "Номер=".$pp->Number."\r\n";
            $reestrtext .= "Дата=".date("d.m.Y", $this->datepp)."\r\n";
            $reestrtext .= "Сумма=".sprintf("%02.2f",($pp->fSumm))."\r\n";
            $reestrtext .= "ПлательщикСчет=".$this->recviz->RaschShet."\r\n";
            $reestrtext .= "Плательщик=ИНН ".$this->recviz->INN."\\".$this->recviz->name."\r\n";
            $reestrtext .= "ПлательщикИНН=".$this->recviz->INN."\r\n";
            $reestrtext .= "ПлательщикКПП=".$this->recviz->KPP."\r\n";
            $reestrtext .= "Плательщик1=".$this->recviz->Name."\r\n";
            $reestrtext .= "ПлательщикРасчСчет=".$this->recviz->RaschShet."\r\n";
            $reestrtext .= "ПлательщикБанк1=".$this->recviz->NameBank."\r\n";
            $reestrtext .= "ПлательщикБанк2=".$this->recviz->SityBank."\r\n";
            $reestrtext .= "ПлательщикБИК=".$this->recviz->BIK."\r\n";
            $reestrtext .= "ПлательщикКорсчет=".$this->recviz->KorShet."\r\n";
            $reestrtext .= "ПолучательСчет=".$pp->RaschShet."\r\n";
            if (empty($pp->INN)) {
                $reestrtext .= "Получатель=" . $pp->Name . "\r\n";
            } else {
                $reestrtext .= "Получатель=ИНН ".$pp->INN." ".$pp->Name."\r\n";
            }
            $reestrtext .= "ПолучательИНН=".$pp->INN."\r\n";
            $reestrtext .= "ПолучательКПП=".$pp->KPP."\r\n";
            $reestrtext .= "Получатель1=".$pp->Name."\r\n";
            $reestrtext .= "ПолучательРасчСчет=".$pp->RaschShet."\r\n";
            $reestrtext .= "ПолучательБанк1=".$pp->NameBank."\r\n";
            $reestrtext .= "ПолучательБанк2=".$pp->SityBank."\r\n";
            $reestrtext .= "ПолучательБИК=".$pp->BIK."\r\n";
            if (trim($pp->KorShet) == 0) {
                $reestrtext .= "ПолучательКорсчет=\r\n";
            } else {
                $reestrtext .= "ПолучательКорсчет=" . $pp->KorShet . "\r\n";
            }
            $reestrtext .= "ВидПлатежа=\r\n";
            $reestrtext .= "ВидОплаты=01\r\n";
            $reestrtext .= "Код=0\r\n";

            if ($pp->PokazKBK != 0) {
                $reestrtext .= "СтатусСоставителя=08\r\n";
                $reestrtext .= "Очередность=5\r\n";
                $reestrtext .= "ПоказательКБК=".$pp->PokazKBK."\r\n";
                $reestrtext .= "ОКАТО=".$pp->OKATO."\r\n";
                $reestrtext .= "ПоказательОснования=0\r\n";
                $reestrtext .= "ПоказательПериода=0\r\n";
                $reestrtext .= "ПоказательНомера=".$pp->Number."\r\n";
                $reestrtext .= "ПоказательДаты=0\r\n";
                $reestrtext .= "ПоказательТипа=0\r\n";
            } else {
                $reestrtext .= "Очередность=5\r\n";
            }

            $reestrtext .= "НазначениеПлатежа1=".$pp->NaznaChenPlatez."\r\n";
            $reestrtext .= "НазначениеПлатежа=".$pp->NaznaChenPlatez."\r\n";
            $reestrtext .= "КонецДокумента\r\n";
        }

        return $reestrtext;
    }

    private function footer()
    {
        return "КонецФайла\r\n";
    }


}