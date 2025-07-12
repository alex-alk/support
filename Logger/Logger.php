<?php

namespace Logger;

use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Psr\Log\LoggerTrait;
use Psr\Log\LogLevel;

class Logger implements LoggerInterface
{
    use LoggerTrait;

    protected string $logFile = __DIR__.'/../../storage/logs/app.log';

    public function log($level, $message, array $context = array())
    {
        $levels = [
            LogLevel::EMERGENCY,
            LogLevel::ALERT,
            LogLevel::CRITICAL,
            LogLevel::ERROR,
            LogLevel::WARNING,
            LogLevel::NOTICE,
            LogLevel::INFO,
            LogLevel::DEBUG,
        ];

        if (!in_array($level, $levels)) {
            throw new InvalidArgumentException("Invalid log level: $level");
        }

        $message = $this->interpolate($message, $context);
        $entry = sprintf("[%s] %s: %s\n", date('Y-m-d H:i:s'), strtoupper($level), $message);

        file_put_contents($this->logFile, $entry, FILE_APPEND);
    }

    /**
     * Interpolates context values into the message placeholders.
     */
    private function interpolate($message, array $context = array())
    {
        // build a replacement array with braces around the context keys
        $replace = array();
        foreach ($context as $key => $val) {
            // check that the value can be cast to string
            if (!is_array($val) && (!is_object($val) || method_exists($val, '__toString'))) {
                $replace['{' . $key . '}'] = $val;
            }
        }

        // interpolate replacement values into the message and return
        return strtr($message, $replace);
    }
}