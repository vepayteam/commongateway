<?php

namespace app\services\queue;

use app\helpers\EnvHelper;
use yii\queue\db\Queue;

// TODO del class
class DbQueueTraceId extends Queue
{
    protected function pushMessage($message, $ttr, $delay, $priority)
    {
        $this->db->createCommand()->insert($this->tableName, [
            'channel' => $this->channel,
            'job' => $message,
            'pushed_at' => time(),
            'ttr' => $ttr,
            'delay' => $delay,
            'priority' => $priority ?: 1024,
            'trace_id' => EnvHelper::getParam(EnvHelper::UNIQUE_ID),
        ])->execute();
        $tableSchema = $this->db->getTableSchema($this->tableName);
        return $this->db->getLastInsertID($tableSchema->sequenceName);
    }
}
