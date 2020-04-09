<?php


namespace app\models\antifraud\rules\refund_card;


use app\models\antifraud\control_objects\refund\RefundSql;
use app\models\antifraud\rules\DataTrait;
use app\models\antifraud\rules\interfaces\IRule;
use app\models\antifraud\rules\interfaces\ISqlRule;
use app\models\antifraud\support_objects\RefundInfo;
use app\models\antifraud\support_objects\TransInfo;
use app\models\antifraud\tables\AFSettings;
use app\models\queue\SendMailJob;
use app\models\SendEmail;
use Faker\Provider\DateTime;
use Yii;
use yii\helpers\VarDumper;


/**
 * @property RefundInfo $trans_info
 * @property AFSettings $setting
*/
class RefundCard implements IRule
{
    use DataTrait;
    private $as_main;
    private $trans_info;
    private $setting;

    public function __construct($trans_info, $as_main = false)
    {
        $this->as_main = $as_main;
        $this->trans_info = $trans_info;
        $this->setting = AFSettings::find()->where(['key'=>'block_email_for_antifraud_refund'])->one();
    }

    /**
     * Указывает должно ли правило выполняться как основное / или как составное
     * @return boolean true - основное, false - состовное
     */
    public function as_main(): bool
    {
        return $this->as_main;
    }

    /** Получает данные из бд посредством выполнения sql запроса */
    public function data(): array
    {
        return $this->data_trait();
    }

    /**
     * Пустой массив передается в случае когда нужно сделать отдельный sql запрос.
     * @param array $datas
     * @return bool
     */
    public function validated(array $datas): bool
    {
        $validated = true;
        foreach ($datas as $data) {
            $unix = \DateTime::createFromFormat("U", time());
            $cur_date = $unix->format('d.m.Y');
            $cur_hour = $unix->format('G');//без ведущего нуля.
            $unix->sub(new \DateInterval('P1D'));
            $minus_day_date = $unix->format('d.m.Y');

            $unix_payed = \DateTime::createFromFormat("U", $data['DateCreate']);
            $payed_date = $unix_payed->format('d.m.Y');
            if ($cur_date == $payed_date) {
                $validated = false;
                break;
            }
            if ($cur_hour < 5 && $minus_day_date == $payed_date) {
                $validated = false;
                break;
            }
        }
        if (!$validated && isset($this->setting->value)) {
            //отправка почту.
            $this->send_email($this->setting->value);
        }
        return $validated;
    }

    /** Генерирует sql для проверки правила */
    public function sql_obj(): ISqlRule
    {
        return new RefundCardSql($this->trans_info);
    }

    /** Возвращает "вес" правила*/
    public function weight(): float
    {
        return 1;
    }

    private function send_email(string $email): void
    {
        if (!empty($email)) {
            $content = 'Заблокирована выплата на карту. Id транзакции = ' . $this->trans_info->transaction_id();
            Yii::$app->queue->push(new SendMailJob([
                'email' => $email,
                'subject' => 'Заблокирована выплата на карту',
                'content' => $content
            ]));
        }
    }
}