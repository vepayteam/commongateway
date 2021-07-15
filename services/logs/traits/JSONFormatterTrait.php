<?php

namespace app\services\logs\traits;

use Exception;
use Yii;
use yii\helpers\VarDumper;
use yii\log\Logger;
use yii\web\Request;


trait JSONFormatterTrait
{
    /**
     * Formats a log message for display as a string.
     * @param array $message the log message to be formatted.
     * The message structure follows that in [[Logger::messages]].
     * @return array the formatted message
     * @throws \Throwable
     * @throws \yii\base\InvalidConfigException
     */
    public function formatMessage($message)
    {
        list($text, $level, $category, $timestamp) = $message;
        $level = Logger::getLevelName($level);
        if (!is_string($text)) {
            // exceptions may not be serializable if in the call stack somewhere is a Closure
            if ($text instanceof \Throwable || $text instanceof Exception) {
                $text = (string)$text;
            } else {
                $text = VarDumper::export($text);
            }
        }
        $traces = [];
        if (isset($message[4])) {
            foreach ($message[4] as $trace) {
                $traces[] = "in {$trace['file']}:{$trace['line']}";
            }
        }

        return array_merge(
            $this->getMessagePrefix($message),
            [
                'timestamp' => $this->getTime($timestamp),
                'loglevel' => $level,
                'category' => $category,
                'message' => "$text" . (empty($traces) ? '' : "\n    " . implode("\n    ", $traces))
            ]
        );
    }

    /**
     * Returns a string to be prefixed to the given message.
     * If [[prefix]] is configured it will return the result of the callback.
     * The default implementation will return user IP, user ID and session ID as a prefix.
     * @param array $message the message being exported.
     * The message structure follows that in [[Logger::messages]].
     * @return array the prefix string
     * @throws \Throwable
     * @throws \yii\base\InvalidConfigException
     */
    public function getMessagePrefix($message): array
    {
        if ($this->prefix !== null) {
            return call_user_func($this->prefix, $message);
        }

        if (Yii::$app === null) {
            return [];
        }

        $request = Yii::$app->getRequest();
        $ip = $request instanceof Request ? $request->getUserIP() : '-';

        /* @var $user \yii\web\User */
        $user = Yii::$app->has('user', true) ? Yii::$app->get('user') : null;
        if ($user && ($identity = $user->getIdentity(false))) {
            $userID = $identity->getId();
        } else {
            $userID = '-';
        }

        /* @var $session \yii\web\Session */
        $session = Yii::$app->has('session', true) ? Yii::$app->get('session') : null;
        $sessionID = $session && $session->getIsActive() ? $session->getId() : '-';

        return [
            'ip' => $ip,
            'user' => $userID,
            'session_id' => $sessionID
        ];
    }

}