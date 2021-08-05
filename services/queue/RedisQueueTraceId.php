<?php

namespace app\services\queue;

use app\helpers\EnvHelper;
use yii\base\NotSupportedException;
use yii\queue\redis\Queue;

class RedisQueueTraceId extends Queue
{
    /**
     * @inheritdoc
     */
    protected function pushMessage($message, $ttr, $delay, $priority)
    {
        if ($priority !== null) {
            throw new NotSupportedException('Job priority is not supported in the driver.');
        }

        $traceId = EnvHelper::getParam(EnvHelper::UNIQUE_ID);

        $id = $this->redis->incr("$this->channel.message_id");
        $this->redis->hset("$this->channel.messages", $id, "$ttr;$message;$traceId");
        if (!$delay) {
            $this->redis->lpush("$this->channel.waiting", $id);
        } else {
            $this->redis->zadd("$this->channel.delayed", time() + $delay, $id);
        }

        return $id;
    }

    /**
     * @inheritdoc
     */
    protected function handleMessage($id, $message, $ttr, $attempt)
    {
        EnvHelper::setParam(EnvHelper::UNIQUE_ID, $this->getTraceId($id));
        return parent::handleMessage($id, $message, $ttr, $attempt);
    }

    /**
     * @inheritdoc
     */
    public function execute($id, $message, $ttr, $attempt, $workerPid)
    {
        EnvHelper::setParam(EnvHelper::UNIQUE_ID, $this->getTraceId($id));
        return parent::execute($id, $message, $ttr, $attempt, $workerPid);
    }

    private function getTraceId($id)
    {
        $payload = $this->redis->hget("$this->channel.messages", $id);
        list(, , $traceId) = explode(';', $payload, 3);

        return $traceId;
    }
}
