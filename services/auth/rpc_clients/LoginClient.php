<?php


namespace app\services\auth\rpc_clients;


use app\services\auth\AuthService;
use app\services\auth\models\LoginForm;
use PhpAmqpLib\Message\AMQPMessage;
use Yii;
use yii\base\Model;

class LoginClient extends BaseClient
{
    protected $queueName = 'login';

    public function call(Model $loginForm)
    {
        $this->response = null;
        $this->corr_id = uniqid();

        $msg = new AMQPMessage(
            serialize($loginForm->asArray()),
            array(
                'correlation_id' => $this->corr_id,
                'reply_to' => $this->callback_queue
            )
        );
        $this->channel->basic_publish($msg, '', $this->$queueName);
        while (!$this->response) {
            $this->channel->wait();
        }
        return unserialize($this->response);
    }



}
