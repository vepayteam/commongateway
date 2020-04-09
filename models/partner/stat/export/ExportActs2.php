<?php


namespace app\models\partner\stat\export;

use app\models\partner\stat\export\excel\cells\BaseCell;
use app\models\partner\stat\export\excel\cells\BorderBottomBoldSolid;
use app\models\partner\stat\export\excel\cells\BorderNormalSolid;
use app\models\partner\stat\export\excel\cells\FontBold;
use app\models\partner\stat\export\excel\cells\FontStyleItalic;
use app\models\partner\stat\export\excel\cells\TextAlignCenter;
use app\models\partner\stat\export\excel\cells\TextAlignRight;
use app\models\partner\stat\export\excel\DocXlsx;
use app\models\partner\stat\export\excel\IExportExcel;
use app\models\partner\stat\export\excel\Line;
use app\models\partner\stat\PayShetStat;
use Faker\Provider\Base;
use Faker\Provider\Text;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use yii\helpers\VarDumper;

class ExportActs2 extends PayShetStat
{
    public function rules()
    {
        $parent = parent::rules(); // TODO: Change the autogenerated stub
        $parent[] = ['IdPart', 'required'];
        return $parent;
    }

    /**
     * Возвращает данные
     */
    public function document()
    {
        //
        $lines = $this->lines();
        $doc = new DocXlsx($lines);
        return $doc->document();
    }

    /**
     * @return Line[] - конфигурация документа
     */
    public function lines(): array
    {
        return [
            new Line([
                new BaseCell(''),
                new FontBold(
                    new TextAlignCenter(
                        new BaseCell('Заголовок', 9)
                    )
                ),
                new BaseCell(''),
            ]),
            new Line([
                new BaseCell(''),
                new FontStyleItalic(
                    new BaseCell('г. Москва', 2)
                ),
                new BaseCell(''),
                new BaseCell(''),
                new BaseCell(''),
                new BaseCell(''),
                new TextAlignRight(
                    new FontStyleItalic(
                        new BaseCell('31 декабря 2019 года', 3)
                    )
                ),
                new BaseCell(''),
            ]),
            new Line(
                [
                    new BaseCell(''),
                    new BaseCell('Общество.', 9),
                    new BaseCell(''),
                ]
            ),
            new Line([
                new BaseCell(''),
                new BorderNormalSolid(
                    new BaseCell('1')
                ),
                new BorderNormalSolid(
                    new BaseCell('Дата, время начала отчетного периода		
', 3)
                ),
                new BorderNormalSolid(
                    new TextAlignCenter(
                        new BaseCell('01.12.2019', 2)
                    )
                ),
                new BorderNormalSolid(
                    new TextAlignCenter(
                        new BaseCell('0:00:00', 3)
                    )
                ),
                new BaseCell(''),
            ]),
            new Line([
                new BaseCell(''),
                new BorderNormalSolid(
                    new BaseCell('2')
                ),
                new BorderNormalSolid(
                    new BaseCell('Дата, время конца отчетного периода', 3)
                ),
                new BorderNormalSolid(
                    new TextAlignCenter(
                        new BaseCell('31.12.2019', 2)
                    )
                ),
                new BorderNormalSolid(
                    new TextAlignCenter(
                        new BaseCell('23:59:59', 3)
                    )
                ),
                new BaseCell('')
            ]),
            new Line([
                new BaseCell(''),
                new BorderNormalSolid(
                    new BaseCell('3')
                ),
                new BorderNormalSolid(
                    new BaseCell('Задолженность Оператора перед Контрагентом на начало Отчетного периода, рубли		
', 3)
                ),
                new BorderNormalSolid(
                    new TextAlignCenter(
                        new BaseCell('2 226 311,10	
', 2)
                    )
                ),
                new BorderNormalSolid(
                    new TextAlignCenter(
                        new BaseCell('Два миллиона двести двадцать шесть тысяч триста одиннадцать рублей 10 копеек		
', 3)
                    )
                ),
                new BaseCell('')
            ]),
            new Line([
                new BaseCell(''),
                new BorderNormalSolid(
                    new BaseCell('4')
                ),
                new BorderNormalSolid(
                    new BaseCell('Задолженность Контрагента  перед Оператором на начало Отчетного периода, рубли		
', 3)
                ),
                new BorderNormalSolid(
                    new TextAlignCenter(
                        new BaseCell('0,00	
', 2)
                    )
                ),
                new BorderNormalSolid(
                    new TextAlignCenter(
                        new BaseCell('Ноль рублей 00 копеек		
', 3)
                    )
                ),
                new BaseCell('')
            ]),
            new Line([
                new BaseCell(''),
                new BorderNormalSolid(
                    new BaseCell('5')
                ),
                new BorderNormalSolid(
                    new BaseCell('Cумма  переводов, принятых Оператором в Отчетном периоде, рубли		
', 3)
                ),
                new BorderNormalSolid(
                    new TextAlignCenter(
                        new BaseCell('6 997 058,86	
', 2)
                    )
                ),
                new BorderNormalSolid(
                    new TextAlignCenter(
                        new BaseCell('Шесть миллионов девятьсот девяносто семь тысяч пятьдесят восемь рублей 86 копеек		
', 3)
                    )
                ),
                new BaseCell('')
            ]),
            new Line([
                new BaseCell(''),
                new BorderNormalSolid(
                    new BaseCell('6')
                ),
                new BorderNormalSolid(
                    new BaseCell('Сумма вознаграждения Оператора за Отчетный период, НДС не облагается в соответствии с пп. 4 п. 3 статьи 149 Налогового кодекса РФ, рубли		
', 3)
                ),
                new BorderNormalSolid(
                    new TextAlignCenter(
                        new BaseCell('699,62	
', 2)
                    )
                ),
                new BorderNormalSolid(
                    new TextAlignCenter(
                        new BaseCell('Шестьсот девяносто девять рублей 62 копейки		
', 3)
                    )
                ),
                new BaseCell('')
            ]),
            new Line([
                new BaseCell(''),
                new BorderNormalSolid(
                    new BaseCell('7')
                ),
                new BorderNormalSolid(
                    new BaseCell('Возвращено Оператором переводов в Отчетном периоде, рубли		
', 3)
                ),
                new BorderNormalSolid(
                    new TextAlignCenter(
                        new BaseCell('0,00	
', 2)
                    )
                ),
                new BorderNormalSolid(
                    new TextAlignCenter(
                        new BaseCell('Ноль рублей 00 копеек		
', 3)
                    )
                ),
                new BaseCell('')
            ]),
            new Line([
                new BaseCell(''),
                new BorderNormalSolid(
                    new BaseCell('8')
                ),
                new BorderNormalSolid(
                    new BaseCell('Подлежит удержанию Оператором по оспариваемым операциям в Отчетном периоде, рубли		
', 3)
                ),
                new BorderNormalSolid(
                    new TextAlignCenter(
                        new BaseCell('0,00	
', 2)
                    )
                ),
                new BorderNormalSolid(
                    new TextAlignCenter(
                        new BaseCell('Ноль рублей 00 копеек		
', 3)
                    )
                ),
                new BaseCell('')
            ]),
            new Line([
                new BaseCell(''),
                new BorderNormalSolid(
                    new BaseCell('9')
                ),
                new BorderNormalSolid(
                    new BaseCell('Подлежит возмещению Оператором по оспариваемым операциям в Отчетном периоде, рубли		
', 3)
                ),
                new BorderNormalSolid(
                    new TextAlignCenter(
                        new BaseCell('0,00	
', 2)
                    )
                ),
                new BorderNormalSolid(
                    new TextAlignCenter(
                        new BaseCell('Ноль рублей 00 копеек		
', 3)
                    )
                ),
                new BaseCell('')
            ]),
            new Line([
                new BaseCell(''),
                new BorderNormalSolid(
                    new BaseCell('10')
                ),
                new BorderNormalSolid(
                    new BaseCell('Перечислено Контрагентом на расчетный счет Оператора, рубли (в т.ч. суммы, перечисленные Оператором на счет Контрагента, отклоненные банком и подлежащие перечислению повторно)		
', 3)
                ),
                new BorderNormalSolid(
                    new TextAlignCenter(
                        new BaseCell('0,00	
', 2)
                    )
                ),
                new BorderNormalSolid(
                    new TextAlignCenter(
                        new BaseCell('Ноль рублей 00 копеек		
', 3)
                    )
                ),
                new BaseCell('')
            ]),
            new Line([
                new BaseCell(''),
                new BorderNormalSolid(
                    new BaseCell('11')
                ),
                new BorderNormalSolid(
                    new BaseCell('Перечислено Оператором на счет Контрагента в Отчетном периоде, рубли		
', 3)
                ),
                new BorderNormalSolid(
                    new TextAlignCenter(
                        new BaseCell('8 789 242,33	
', 2)
                    )
                ),
                new BorderNormalSolid(
                    new TextAlignCenter(
                        new BaseCell('Восемь миллионов семьсот восемьдесят девять тысяч двести сорок два рубля 33 копейки		
', 3)
                    )
                ),
                new BaseCell('')
            ]),
            new Line([
                new BaseCell(''),
                new BorderNormalSolid(
                    new BaseCell('12')
                ),
                new BorderNormalSolid(
                    new BaseCell('Перечислено Оператором на счет по учету обеспечения Контрагента в соответствии с Соглашением в Отчетном периоде, рубли		
', 3)
                ),
                new BorderNormalSolid(
                    new TextAlignCenter(
                        new BaseCell('0,00	
', 2)
                    )
                ),
                new BorderNormalSolid(
                    new TextAlignCenter(
                        new BaseCell('Ноль рублей 00 копеек		
', 3)
                    )
                ),
                new BaseCell('')
            ]),
            new Line([
                new BaseCell(''),
                new BorderNormalSolid(
                    new BaseCell('13')
                ),
                new BorderNormalSolid(
                    new BaseCell('Задолженность Оператора перед Контрагентом на конец Отчетного периода, рубли		
', 3)
                ),
                new BorderNormalSolid(
                    new TextAlignCenter(
                        new BaseCell('433 428,01	
', 2)
                    )
                ),
                new BorderNormalSolid(
                    new TextAlignCenter(
                        new BaseCell('Четыреста тридцать три тысячи четыреста двадцать восемь рублей 01 копейка		
', 3)
                    )
                ),
                new BaseCell('')
            ]),
            new Line([
                new BaseCell(''),
                new BorderNormalSolid(
                    new BaseCell('14')
                ),
                new BorderNormalSolid(
                    new BaseCell('Задолженность Контрагента перед Оператором на конец Отчетного периода, рубли		
', 3)
                ),
                new BorderNormalSolid(
                    new TextAlignCenter(
                        new BaseCell('0,00	
', 2)
                    )
                ),
                new BorderNormalSolid(
                    new TextAlignCenter(
                        new BaseCell('Ноль рублей 00 копеек		
', 3)
                    )
                ),
                new BaseCell('')
            ]),
            new Line([
                new BaseCell(''),
                new BaseCell(''),
                new BaseCell(''),
                new FontBold(
                    new BaseCell('Таблица расшифровки платежей		
', 3)
                ),
                new BaseCell(''),
                new BaseCell(''),
                new BaseCell(''),
                new BaseCell(''),
            ]),
            'хедер расшифровки'=>new Line([
                new BaseCell(''),
                new FontBold(
                    new BorderNormalSolid(
                        new BaseCell('№ п/п
')
                    )
                ),
                new FontBold(
                    new BorderNormalSolid(
                        new BaseCell('Способ осуществления перевода плательщиком', 2)
                    )
                ),
                new FontBold(
                    new TextAlignCenter(
                        new BorderNormalSolid(
                            new BaseCell('Сумма переводов, принятых Оператором в Отчетном периоде', 2)
                        )
                    )
                ),
                new FontBold(
                    new TextAlignCenter(
                        new BorderNormalSolid(
                            new BaseCell('Возвращено Оператором переводов физическим лицам в Отчетном периоде', 2)
                        )
                    )
                ),
                new FontBold(
                    new TextAlignCenter(
                        new BorderNormalSolid(
                            new BaseCell('Сумма вознаграждения Оператора, руб. (НДС не облагается)', 2)
                        )
                    )
                )
            ]),
            'соглашение' => new Line([]),
            'соглашение1'=>new Line([
                new BaseCell(''),
                new BaseCell('1. Стороны претензий друг к другу не имеют.', 9),
                new BaseCell(''),
            ]),
            new Line([
                new BaseCell(''),
                new BaseCell('2. Настоящий Акт составлен, утвержден и подписан в двух экземплярах, имеющих одинаковую юридическую силу, по одному для Оператора и Контрагента.', 9),
                new BaseCell('')
            ]),
            new Line([]),
            new Line([
                new BaseCell(''),
                new TextAlignCenter(
                    new FontBold(
                        new BaseCell('ПОДПИСИ СТОРОН:', 9)
                    )
                ),
                new BaseCell('')
            ]),
            new Line([]),
            new Line([
                new BaseCell(''),
                new FontBold(
                    new TextAlignCenter(
                        new BaseCell('М.П.',2)
                    )
                ),
                new BaseCell('',5),
                new FontBold(
                    new TextAlignCenter(
                        new BaseCell('М.П.',2)
                    )
                ),
                new BaseCell(''),
            ]),
            new Line([]),
            new Line([]),
            new Line([
                new BaseCell(''),
                new BorderBottomBoldSolid(
                    new BaseCell('Устюжанин Д.Н.',2)
                ),
                new BaseCell('',5),
                new BorderBottomBoldSolid(
                    new BaseCell('', 2)
                )
            ])
        ];
    }
}