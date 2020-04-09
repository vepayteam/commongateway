<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\mail\MailerInterface;

/**
 * @property MailerInterface $mailer
 */
class SendEmail extends Model
{
    public $fromEmail = 'robot@vepay.online';

    public $mailer; //иногда необходимо заменить аккаунт отправки почты.

    public function init()
    {
        if (!$this->mailer) {
            $this->mailer = Yii::$app->mailer;
        }
    }

    /**
     * Отправка email
     * @param array|string $email
     * @param array|string $emailfrom
     * @param string $subject
     * @param string $content
     * @return bool
     */
    public function send($email, $emailfrom, $subject, $content)
    {
        $emailfrom = [empty($emailfrom) ? $this->fromEmail : $emailfrom => 'Vepay'];
        if (Yii::$app->params['TESTMODE'] == "Y") {
            $email = "support@teleport.run";
        }
        $this->mailer->compose('@app/mail/layouts/html', ['content' => $content])
            ->setTo($this->explodeMail($email))
            ->setFrom($emailfrom)
            ->setSubject($subject)
            ->send();
        return true;
    }

    /**
     * Отправка реестров
     * @param array|string $email
     * @param string $subject
     * @param string $content
     * @param array $files array['data','name']
     * @return bool
     */
    public function sendReestr($email, $subject, $content, $files = [])
    {
        $emailfrom = [$this->fromEmail => 'Vepay'];
        if (Yii::$app->params['TESTMODE'] == "Y") {
            $email = "support@teleport.run";
        }
        $mailer = $this->mailer->compose('@app/mail/layouts/html', ['content' => $content])
            ->setTo($this->explodeMail($email))
            ->setFrom($emailfrom)
            ->setSubject($subject);
        foreach ($files as $file) {
            $mailer = $mailer->attachContent($file['data'], ['fileName' => $file['name']]);
        }
        return $mailer->send();
    }

    /**
     * Список адресов через запятую - в массив
     * @param string|array $email
     * @return array
     */
    private function explodeMail($email)
    {
        $ret = $email;
        if (is_string($email) && stripos($email, ",")) {
            $ret = explode(",", $email);
        }
        return $ret;
    }
}