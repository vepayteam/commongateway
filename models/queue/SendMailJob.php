<?php


namespace app\models\queue;


use app\models\SendEmail;
use Yii;
use yii\base\BaseObject;

class SendMailJob extends BaseObject implements \yii\queue\JobInterface
{
    public $email = '';
    public $fromEmail = '';
    public $subject = '';
    public $content = '';
    public $attach; //array['filename','name']

    /**
     * @param \yii\queue\Queue $queue
     */
    public function execute($queue)
    {
        $sender = new SendEmail();
        if (!empty($this->fromEmail)) {
            $sender->fromEmail = $this->fromEmail;
        }

        $files = [];
        if (is_array($this->attach)) {
            foreach ($this->attach as $attach) {
                $data = file_get_contents($attach['filename']);
                $files[] = ['data' => $data, 'name' => $attach['name']];
            }
        }

        $res = $sender->sendReestr($this->email, $this->subject, $this->content, $files);

        if ($res && is_array($this->attach)) {
            foreach ($this->attach as $attach) {
                @unlink($attach['filename']);
            }
        }

        Yii::warning("SendMailJob '".$this->subject."' res=".$res, 'rsbcron');
    }

}