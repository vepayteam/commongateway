<?php

namespace app\services\queue;

use app\helpers\EnvHelper;
use yii\queue\redis\Queue;

class RedisQueueTraceId extends Queue
{
    /**
     * @inheritdoc
     */
    protected function pushMessage($message, $ttr, $delay, $priority)
    {
        $id = parent::pushMessage($message, $ttr, $delay, $priority);

        $traceId = EnvHelper::getParam(EnvHelper::UNIQUE_ID);
        $this->redis->hset("$this->channel.trace_id", $id, $traceId);

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
        return $this->redis->hget("$this->channel.trace_id", $id);
    }
}
