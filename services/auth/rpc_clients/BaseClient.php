<?php


namespace app\services\auth\rpc_clients;


use app\services\auth\AuthService;
use Yii;
use yii\base\Model;

abstract class BaseClient
{
    /** @var null|string  */
    protected $queueName = null;

    protected $channel;
    protected $callback_queue;
    protected $response;
    protected $corr_id;

    public function __construct()
    {
        $connection = $this->getAuthService()->getConnection();
        $this->channel = $connection->channel();
        list($this->callback_queue, ,) = $this->channel->queue_declare(
            "",
            false,
            false,
            true,
            false
        );
        $this->channel->basic_consume(
            $this->callback_queue,
            '',
            false,
            true,
            false,
            false,
            array(
                $this,
                'onResponse'
            )
        );
    }

    public function onResponse($rep)
    {
        if ($rep->get('correlation_id') == $this->corr_id) {
            $this->response = $rep->body;
        }
    }

    abstract function call(Model $model);

    /**
     * @return AuthService
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\di\NotInstantiableException
     */
    protected function getAuthService()
    {
        return Yii::$container->get('AuthService');

    }

}
