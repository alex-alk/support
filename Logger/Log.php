<?php

namespace Logger;

use Psr\Log\LoggerInterface;

/**
 * Log Facade
 */
class Log
{
    private static function getLogger(): LoggerInterface
    {
        return new Logger();
    }

    public static function debug(mixed $message): void
    {
        self::getLogger()->debug($message);
    }

    public static function info(mixed $message, $context = []): void
    {
        self::getLogger()->info($message, $context);
    }

    public static function notice(mixed $message): void
    {
        self::getLogger()->notice($message);
    }

    public static function warning(mixed $message): void
    {
        self::getLogger()->warning($message);
    }

    public static function error(mixed $message, $context = []): void
    {
        self::getLogger()->error($message, $context);
    }

    public static function critical(mixed $message): void
    {
        self::getLogger()->critical($message);
    }

    public static function alert(mixed $message): void
    {
        self::getLogger()->alert($message);
    }

    public static function emergency(mixed $message): void
    {
        self::getLogger()->emergency($message);
    }
}