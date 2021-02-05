<?php

namespace Vepay\Gateway\Logger\Handler;

/**
 * Class File
 * @package Vepay\Gateway\Logger\Handler
 */
class File implements HandlerInterface
{
    /**
     * @var string
     */
    protected $logFile;

    /**
     * File constructor.
     * @param string $logFile
     */
    public function __construct(string $logFile = 'default.log')
    {
        $this->logFile = $logFile;
    }

    /**
     * @param string $level
     * @param string $category
     * @param $message
     */
    public function handle(string $level, string $category, $message)
    {
        $logLine = $this->formatLogLine($level, $category, $message);
        $result = @file_put_contents($this->logFile, $logLine, FILE_APPEND);

        if ($result === false) {
            throw new \RuntimeException("Cannot write to {$this->logFile}");
        }
    }

    /**
     * @param string $level
     * @param string $category
     * @param $message
     * @return string
     * @throws \Exception
     */
    protected function formatLogLine(string $level, string $category, $message)
    {
        $time = microtime(true);
        $timeFormatted = sprintf("%06d", ($time - floor($time)) * 1000000);
        $dt = new \DateTime(date('Y-m-d H:i:s.' . $timeFormatted, $time));
        $time = $dt->format('Y-m-d H:i:s.u');

        return
            $time . "\t" .
            "[{$level}]" . "\t" .
            "[{$category}]" . "\t" .
            str_replace(["\r\n", "\n", "\r"], '\n', trim($message)) . "\t" . \PHP_EOL;
    }
}
