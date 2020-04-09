<?php


namespace app\models\sms;


use app\models\sms\tables\Sms;
use Yii;

/**
 * @property array  $errors
 * @property string $code
 * @property bool   $confirmed
 * @property Sms    $smsRecord
 */
class ConfirmCode implements ICode
{
    private $code;
    private $errors;
    private $confirmed = false;
    private $smsRecord;

    public function __construct(string $code)
    {
        $this->code = $code;
    }

    /**
     * @return bool
     */
    public function confirmed(): bool
    {
        $this->confirmed = true;
        $sms = Sms::find()->where([
            'code' => $this->code,
            'partner_id' => Yii::$app->user->identity->getPartner()
        ])->one();
        /**@var Sms $sms */
        if ($sms and $sms->confirm == 0) {
            $this->smsRecord = $sms;
            return true;
        } else {
            $this->addError(400, 'Не верно введен код.');
        }
        return false;
    }

    private function addError(int $errorCode, $message): void
    {
        $this->errors[] = ['code' => $errorCode, 'message' => $message];
    }

    public function errors(): array
    {
        if (!$this->confirmed) {
            $this->confirmed();
        }
        if (!$this->errors) {
            return [];
        }
        return $this->errors;
    }

    /**
     * Проверяет код сразу
     *
     * @param string $code
     *
     * @return ConfirmCode
     */
    public static function buildAjax(string $code): self
    {
        $Code = new self($code);
        foreach ($Code->errors() as $error) {
            Stop::app($error['code'], $error['message']);
        }
        return $Code;
    }

    public function code(): string
    {
        return $this->code;
    }


    /**
     * Возвращает модель SMS к которой принадлежит данный код.
     * @return Sms
     */
    public function sms()
    {
        return $this->smsRecord;
    }
}