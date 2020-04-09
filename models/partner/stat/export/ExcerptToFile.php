<?php


namespace app\models\partner\stat\export;


use app\models\partner\stat\Excerpt;
use kartik\mpdf\Pdf;
use yii\web\Controller;

/**
 * @property Excerpt $excerpt
 * @property Pdf $pdf;
*/
class ExcerptToFile implements IExport
{

    private $excerpt;
    private $pdf;

    public function __construct(Excerpt $excerpt, Pdf $pdf) {
        $controller = new Controller('stat', 'partner');
        $controller->viewPath = "@app/modules/partner/views/stat";
        $this->pdf = $pdf;
        $this->pdf->content = $controller->renderPartial('excerpt/_excerpt-to-pdf', ['data'=>$excerpt->data()]);
        $this->excerpt = $excerpt;
    }

    public static function buildSimpleOrderId(int $id): self
    {
        return new self(
            new Excerpt($id),
            new Pdf([
                'mode'=> Pdf::MODE_UTF8,
                'format'=>Pdf::FORMAT_A4,
                'orientation'=>Pdf::ORIENT_PORTRAIT,
                'destination' => Pdf::DEST_DOWNLOAD,
                'cssFile' => '@vendor/kartik-v/yii2-mpdf/src/assets/kv-mpdf-bootstrap.min.css',
                'cssInline'=>'tr td:nth-child(2){text-align:right; padding-top: 15px; height: 50px;} tr td{border-bottom: 1px solid #f2f2f2; height: 50px; padding-top: 15px;}'
            ])
        );
    }

    public function content(){
        return $this->pdf->render();
    }

    /**
     * Указывает валиден ли экспорт и входные данные.
     */
    public function validated(): bool
    {
        if($this->excerpt->data()){
            return true;
        }
        return false;
    }

    /**
     * Возвращает обработчик (объект), который трудился над созданием конкретного формата эксопрта.
     */
    public function handler(): Pdf
    {
        return $this->pdf;
    }

    /**
     * Возвращает источник исходных данных по которым строился экспорт.
     */
    public function dataSource(): array
    {
        return $this->excerpt->data();
    }
}