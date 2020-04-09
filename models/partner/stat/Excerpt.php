<?php


namespace app\models\partner\stat;


use app\models\partner\UserLk;
use Faker\Provider\DateTime;
use kartik\mpdf\Pdf;
use Yii;
use yii\db\Query;

class Excerpt
{
    private $id;
    private $dataDb;
    private $requestedToDb = false;

    public function __construct(int $id) {
        $this->id = $id;
    }

    public static function buildAjax(string $fieldName): self
    {
        $id = (int) Yii::$app->request->post($fieldName);
        return new self($id);
    }

    /**
     * Возвращает преобразованные данные (например маскирует номер карты и пр.)
    */
    public function data(): array
    {
        if($this->dataFromDb()){
            $data = $this->dataFromDb();
            $data['DateCreate'] = $this->convertToDate($data['DateCreate']);
            $data['DateOplat'] = $this->convertToDate($data['DateOplat']);
            return $data;
        }
        return [];
    }

    public function excerptName():string
    {
        return $this->data()['NameUsluga'];
    }

    /**
     * Возвращает информацию из бд, для экспорта одной выписки
     * @return array
    */
    private function dataFromDb(): array
    {
        if (!$this->dataDb and !$this->requestedToDb) {
            $this->requestedToDb = true; //чтобы по нескольку раз не делать запрос к бд.
            $query = new Query();
            $query
                ->from('pay_schet as o')
                ->leftJoin('uslugatovar as u', 'u.ID = o.IdUsluga')
                ->select('
                o.ErrorInfo, 
                o.DateCreate, 
                o.DateOplat, 
                o.Status, 
                o.SummPay,
                o.ExtBillNumber, 
                o.ID,
                o.RRN,
                o.CardType,
                o.CardNum,
                o.CardHolder,
                o.CardExp,
                u.NameUsluga
                ');

            if (UserLk::IsAdmin(Yii::$app->user)){
                $query->where(['o.ID'=>$this->id]);
            }else{
                $query->where(['o.ID'=>$this->id, 'idOrg'=>Yii::$app->user->identity->getPartner()]);
            }
            $this->dataDb = $query->one();
        }
        return $this->dataDb;
    }

    private function convertToDate(int $stamp): string
    {
        if($stamp){
            return \DateTime::createFromFormat('U', $stamp)->format('Y-m-d / H:i:s');
        }
        return '';
    }
}