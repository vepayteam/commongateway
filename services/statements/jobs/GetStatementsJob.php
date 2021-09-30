<?php


namespace app\services\statements\jobs;


use app\models\mfo\statements\ReceiveStatemets;
use app\models\payonline\Partner;
use app\services\payment\banks\bank_adapter_responses\BaseResponse;
use app\services\payment\banks\bank_adapter_responses\GetStatementsResponse;
use app\services\payment\banks\BankAdapterBuilder;
use app\services\payment\forms\GetStatementsForm;
use app\services\payment\models\Bank;
use app\services\statements\models\StatementsAccount;
use app\services\statements\models\StatementsPlanner;
use Yii;
use yii\base\BaseObject;
use yii\db\Exception;

class GetStatementsJob extends BaseObject implements \yii\queue\JobInterface
{
    public $IdPartner;
    public $bankId;
    public $TypeAcc;
    public $datefrom;
    public $dateto;

    /**
     * @param \yii\queue\Queue $queue
     */
    public function execute($queue)
    {
        $partner = Partner::findOne(['ID' => $this->IdPartner]);
        $bank = Bank::findOne(['ID' => $this->bankId]);
        $bankAdapterBuilder = new BankAdapterBuilder();
        $bankAdapterBuilder->buildByBankAndAccountType($partner, $bank, $this->TypeAcc);

        $bankAdapter = $bankAdapterBuilder->getBankAdapter();

        $getStatementsForm = new GetStatementsForm();
        $getStatementsForm->dateFrom = $this->datefrom;
        $getStatementsForm->dateTo = $this->dateto;

        $getStatementsResponse = $bankAdapter->getStatements($getStatementsForm);

        if($getStatementsResponse->status == BaseResponse::STATUS_DONE) {
            $this->updateStatements($getStatementsResponse);
        }
    }

    /**
     * @param GetStatementsResponse $getStatementsResponse
     */
    protected function updateStatements(GetStatementsResponse $getStatementsResponse)
    {
        $tr = Yii::$app->db->beginTransaction();

        try {
            foreach ($getStatementsResponse->statements as $statement) {
                $this->iterUpdateStatements($statement);
            }
            $statementsPlanner = new StatementsPlanner();
            $statementsPlanner->IdPartner = $this->IdPartner;
            $statementsPlanner->IdTypeAcc = $this->TypeAcc;
            $statementsPlanner->DateUpdateFrom = strtotime($this->datefrom);
            $statementsPlanner->DateUpdateTo = strtotime(min($this->dateto, time() - 60));
            $statementsPlanner->save(false);

            $tr->commit();
        } catch (\Exception $e) {
            Yii::error('GetStatementsJob error update:' . $e->getMessage());
            $tr->rollBack();
        }
    }

    /**
     * @param array $statement
     * @return bool
     */
    protected function iterUpdateStatements($statement)
    {
        $existStatement = StatementsAccount::find()->where([
            'IdPartner' => $this->IdPartner,
            'TypeAccount' => $this->TypeAcc,
            'BnkId' => $statement['id'],
            'BankId' => $this->bankId,
        ])->exists();

        if($existStatement) {
            return true;
        }

        $inn = $statement['inn'] ?? '';
        $description = $statement['description'];
        if ($inn == '7709129705') {
            // TODO: заменить ТКБ на Vepay и прибвать комиссию
            $name = 'ООО "ПКБП"';
            $inn = '7728487400';
            $description = $this->changeDescript($description);
        } elseif ($inn == '7707083893' || $inn == '7744001497') {

            //сбербанк,гпб - подставить реквизиты из name и назначения
            $n = explode('//', $statement['name']);
            if (count($n) > 2) {
                $name = $n[1].(isset($n[3]) ? '//'.$n[3].'//' : '');
            }
            if (preg_match('/ИНН\s+(\d+)/ius', $description, $d)) {
                $inn = $d[1];
            }
        } elseif (empty($inn) || $inn == 0) {
            //нет инн в пп, взять из назначения
            if (preg_match('/ИНН\s+(\d+)/ius', $description, $d)) {
                $inn = $d[1];
            }
        } else {
            $name = $statement['name'] ?? '';
        }

        $statementsAccount = new StatementsAccount();
        $statementsAccount->IdPartner = $this->IdPartner;
        $statementsAccount->TypeAccount = $this->TypeAcc;
        $statementsAccount->BnkId = $statement['id'];
        $statementsAccount->NumberPP = $statement['number'];
        $statementsAccount->DatePP = $statement['date'];
        $statementsAccount->DateDoc = $statement['datedoc'];
        $statementsAccount->DateRead = time();
        $statementsAccount->SummPP = round($statement['summ'] * 100.0);
        $statementsAccount->SummComis = 0;
        $statementsAccount->Description = mb_substr($description, 0, 500);
        $statementsAccount->Name = $name ?? '';
        $statementsAccount->Inn = $inn ?? '';
        $statementsAccount->Kpp = $statement['kpp'] ?? '';
        $statementsAccount->Account = $statement['account'];
        $statementsAccount->Bic = $statement['bic'] ?? '';
        $statementsAccount->Bank = $statement['bank'] ?? '';
        $statementsAccount->BankAccount = $statement['bankaccount'] ?? '';

        return $statementsAccount->save(false);
    }

    /**
     * @param $description
     * @return string|string[]|null
     */
    private function changeDescript($description)
    {
        $ret = $description;
        if (mb_strripos($description, 'Комиссия Банка') !== false) {
            $ret = str_ireplace('Комиссия Банка', 'Комиссия Vepay', $description);
        } elseif (mb_strripos($description, 'Комиссия по операциям') !== false) {
            $ret = preg_replace('/за вычетом комиссии [\d+\.]+/ius', '', $description);
        }

        return $ret;
    }

}
