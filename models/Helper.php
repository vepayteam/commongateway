<?php

namespace app\models;

class Helper
{
    public static function ConvertVolumeInformation($Stream)
    {
        if ($Stream >= 1073741824) {
            // в гигабайтах
            $Stream = sprintf('%.1f', $Stream / 1073741824) . " Гб.";
        } elseif ($Stream >= 1048576) {
            // в мегабайтах
            $Stream = sprintf('%.1f', $Stream / 1048576) . " Мб.";
        } elseif ($Stream >= 1024) {
            // в килобайтах
            $Stream = sprintf('%.1f', $Stream / 1024) . " Кб.";
        } else {
            $Stream = $Stream . " б.";
        }

        return $Stream;
    }

    public static function num2strKopText($summ, $minusok = true)
    {
        //$summ - в копейках
        $minusStartSemant = "";
        if ($summ < 0 && $minusok)
            $minusStartSemant = "минус ";
        $summ = abs($summ);
        $summStartrub = floor($summ / 100.0);
        $summStartkop = round(($summ / 100.0 - $summStartrub) * 100.0, 0);
        $summStartkop = sprintf("%02d", $summStartkop);
        $num2strsumm = self::intnum2str($summStartrub);
        if (!empty($minusStartSemant)) {
            $num2strsumm = trim($num2strsumm);
        }
        return $minusStartSemant . $num2strsumm . " " . $summStartkop . " коп.";
    }

    protected static function intnum2str($L)
    {
        //внутренняя функция
        $namerub[1] = " рубль";
        $namerub[2] = " рубля";
        $namerub[3] = " рублей";

        $nametho[1] = "тысяча ";
        $nametho[2] = "тысячи ";
        $nametho[3] = "тысяч ";

        $namemil[1] = "миллион ";
        $namemil[2] = "миллиона ";
        $namemil[3] = "миллионов ";

        $namemrd[1] = "миллиард ";
        $namemrd[2] = "миллиарда ";
        $namemrd[3] = "миллиардов ";

        $s = "";
        $s1 = " ";
        $s2 = " ";
        $kop = (int)($L * 100 - (int)$L * 100);
        $L = (int)($L);
        if ($L >= 1000000000) {
            $many = 0;
            self::semantic((int)($L / 1000000000), $s1, $many, 3);
            $s .= $s1 . $namemrd[$many];
            $L %= 1000000000;
        }

        if ($L >= 1000000) {
            $many = 0;
            self::semantic((int)($L / 1000000), $s1, $many, 2);
            $s .= $s1 . $namemil[$many];
            $L %= 1000000;
            if ($L == 0) {
                $s = trim($s);
                $s .= " рублей";
            }
        }

        if ($L >= 1000) {
            $many = 0;
            self::semantic((int)($L / 1000), $s1, $many, 1);
            $s .= $s1 . $nametho[$many];
            $L %= 1000;
            if ($L == 0) {
                $s = trim($s);
                $s = trim($s) . " рублей";
            }
        }

        if ($L != 0) {
            $many = 0;
            self::semantic($L, $s1, $many, 0);
            $s1 = trim($s1);
            $s .= $s1 . $namerub[$many];
        }

        if ($L == 0 && trim($s) == "") {
            $s = trim($s);
            $s .= "ноль рублей";
        }

        return $s;
    }

    protected static function semantic($i,&$words,&$fem,$f)
    {
        $_1_2[1]="одна ";
        $_1_2[2]="две ";
        $_1_19[1]="один ";
        $_1_19[2]="два ";
        $_1_19[3]="три ";
        $_1_19[4]="четыре ";
        $_1_19[5]="пять ";
        $_1_19[6]="шесть ";
        $_1_19[7]="семь ";
        $_1_19[8]="восемь ";
        $_1_19[9]="девять ";
        $_1_19[10]="десять ";
        $_1_19[11]="одиннадцать ";
        $_1_19[12]="двенадцать ";
        $_1_19[13]="тринадцать ";
        $_1_19[14]="четырнадцать ";
        $_1_19[15]="пятнадцать";
        $_1_19[16]="шестнадцать ";
        $_1_19[17]="семнадцать ";
        $_1_19[18]="восемнадцать ";
        $_1_19[19]="девятнадцать ";

        $des[2]="двадцать ";
        $des[3]="тридцать ";
        $des[4]="сорок ";
        $des[5]="пятьдесят ";
        $des[6]="шестьдесят ";
        $des[7]="семьдесят ";
        $des[8]="восемьдесят ";
        $des[9]="девяносто ";

        $hang[1]="сто ";
        $hang[2]="двести ";
        $hang[3]="триста ";
        $hang[4]="четыреста ";
        $hang[5]="пятьсот ";
        $hang[6]="шестьсот ";
        $hang[7]="семьсот ";
        $hang[8]="восемьсот ";
        $hang[9]="девятьсот ";

        $words="";
        $fl=0;
        if($i >= 100){
            $jkl = (int)($i / 100);
            $words.=$hang[$jkl];
            $i%=100;
        }
        if($i >= 20){
            $jkl = (int)($i / 10);
            $words.=$des[$jkl];
            $i%=10;
            $fl=1;
        }
        switch($i){
            case 1: $fem=1; break;
            case 2:
            case 3:
            case 4: $fem=2; break;
            default: $fem=3; break;
        }
        if( $i ){
            if( $i < 3 && $f > 0 ){
                if ( $f >= 2 ) {
                    $words.=$_1_19[$i];
                }
                else {
                    $words.=$_1_2[$i];
                }
            }
            else {
                $words.=$_1_19[$i];
            }
        }
    }

    public static function num2str($inn, $isset_valuta=true, $stripkop=false, $summabs = false)
    {
        //$inn - сумма в рублях

        $minusStartSemant = "";
        if ($inn < 0 && !$summabs)
            $minusStartSemant = "минус ";

        $nol = 'ноль';
        $str[100]= ['','сто','двести','триста','четыреста','пятьсот','шестьсот', 'семьсот', 'восемьсот','девятьсот'];
        $str[11] = ['','десять','одиннадцать','двенадцать','тринадцать', 'четырнадцать','пятнадцать','шестнадцать','семнадцать', 'восемнадцать','девятнадцать','двадцать'];
        $str[10] = ['','десять','двадцать','тридцать','сорок','пятьдесят', 'шестьдесят','семьдесят','восемьдесят','девяносто'];
        $sex = [
            ['','один','два','три','четыре','пять','шесть','семь', 'восемь','девять'],// m
            ['','одна','две','три','четыре','пять','шесть','семь', 'восемь','девять'] // f
        ];
        if($isset_valuta) {
            $forms = [
                ['копейка', 'копейки', 'копеек', 1], // 10^-2
                ['рубль', 'рубля', 'рублей',  0], // 10^ 0
                ['тысяча', 'тысячи', 'тысяч', 1], // 10^ 3
                ['миллион', 'миллиона', 'миллионов',  0], // 10^ 6
                ['миллиард', 'миллиарда', 'миллиардов',  0], // 10^ 9
                ['триллион', 'триллиона', 'триллионов',  0], // 10^12
            ];
        } else {
            $forms = [
                ['', '', '', 1], // 10^-2
                ['', '', '',  0], // 10^ 0
                ['', '', '', 1], // 10^ 3
                ['', '', '',  0], // 10^ 6
                ['', '', '',  0], // 10^ 9
                ['', '', '',  0], // 10^12
            ];
        }
        $out = $tmp = [];
        // Поехали!
        $tmp = explode('.', str_replace(',','.', $inn));
        $rub = number_format($tmp[0], 0,'','-');
        if ($rub==0) $out[] = $nol;
        // нормализация копеек
        $kop = isset($tmp[1]) ? mb_substr(str_pad($tmp[1], 2, '0', STR_PAD_RIGHT), 0,2) : '00';
        $segments = explode('-', $rub);
        $offset = sizeof($segments);
        if ((int)$rub==0) { // если 0 рублей
            $o[] = $nol;
            $o[] = self::morph(0, $forms[1][0], $forms[1][1], $forms[1][2]);
        } else {
            foreach ($segments as $k=>$lev) {
                $sexi= (int) $forms[$offset][3]; // определяем род
                $ri = (int) $lev; // текущий сегмент
                if ($ri==0 && $offset>1) {// если сегмент==0 & не последний уровень(там Units)
                    $offset--;
                    continue;
                }
                // нормализация
                $ri = str_pad($ri, 3, '0', STR_PAD_LEFT);
                // получаем циферки для анализа
                $r1 = (int)mb_substr($ri, 0, 1); //первая цифра
                $r2 = (int)mb_substr($ri, 1, 1); //вторая
                $r3 = (int)mb_substr($ri, 2, 1); //третья
                $r22= (int)$r2.$r3; //вторая и третья
                // разгребаем порядки
                if ($ri>99) $o[] = $str[100][$r1]; // Сотни
                if ($r22>20) {// >20
                    $o[] = $str[10][$r2];
                    $o[] = $sex[ $sexi ][$r3];
                }
                else { // <=20
                    if ($r22>9) $o[] = $str[11][$r22-9]; // 10-20
                    elseif($r22> 0) $o[] = $sex[ $sexi ][$r3]; // 1-9
                }
                // Рубли
                $o[] = self::morph($ri, $forms[$offset][0],$forms[$offset][1],$forms[$offset][2]);
                $offset--;
            }
        }
        // Копейки
        if (!$stripkop) {
            $o[] = $kop;
            $o[] = self::morph($kop,$forms[0][0],$forms[0][1],$forms[0][2]);
        }
        return $minusStartSemant.preg_replace("/\s{2,}/",' ',implode(' ',$o));
    }

    protected static function morph($n, $f1, $f2, $f5)
    {
        $n = abs($n) % 100;
        $n1 = $n % 10;
        if ($n > 10 && $n < 20) return $f5;
        if ($n1 > 1 && $n1 < 5) return $f2;
        if ($n1 == 1) return $f1;
        return $f5;
    }

    public static function months($month, $zag = false)
    {
        $mnts = ["", "Январь", "Февраль", "Март", "Апрель", "Май", "Июнь", "Июль", "Август", "Сентябрь", "Октябрь", "Ноябрь", "Декабрь"];
        return isset($mnts[$month]) ? ($zag ? $mnts[$month] : mb_strtolower($mnts[$month])) : '';
    }

    public static function monthsScl($month)
    {
        $mnts = ["", "января", "февраля", "марта", "апреля", "мая", "июня", "июля", "августа", "сентября", "октября", "ноября", "декабря"];
        return isset($mnts[$month]) ? $mnts[$month] : '';
    }

    /**
     * UnixTime в строку периода
     * @param int $date дата
     * @return string
     */
    public static function DateRus($date, $i = 0) {
        if ($i) {
            return date("d", $date) . " " . self::months(date("n", $date)) . " " . date("Y", $date);
        } else {
            return date("d", $date) . " " . self::monthsScl(date("n", $date)) . " " . date("Y", $date);
        }
    }

    public static function PregMatch($pattern, $str, &$match)
    {
        if (preg_match(
            iconv("UTF-8", "windows-1251", $pattern),
            iconv("UTF-8", "windows-1251", $str),
            $match)) {
            foreach ($match as &$m) {
                $m = iconv("windows-1251", "UTF-8", $m);
            }
            return true;
        }
        return false;
    }

    public static function xl($inxd)
    {
        $Alpha = ["A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z"];
        if ($inxd < count($Alpha)) {
            return $Alpha[$inxd];
        } else {
            $ret = "";
            $ix = $inxd;
            do {
                $ost = $ix % count($Alpha);
                $ix = $ix / count($Alpha);
                $ret = $Alpha[$ost] . $ret;
            } while ($ix > count($Alpha));
            $ret = $Alpha[$ix - 1] . $ret;
            return $ret;
        }
    }

    public static function ReplaceZapyatoi($arr)
    {
        foreach($arr as $k => $v) {
            if (is_array($v)) {
                $arr[$k] = self::ReplaceZapyatoi($v);
            } else {
                $arr[$k] = str_ireplace(",", ".", $v);
                $arr[$k] = $arr[$k] * 1;
            }
        }
        return $arr;
    }

}