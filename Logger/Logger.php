<?php

namespace Logger;

use DateTime;
use Exception;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

class Logger implements LoggerInterface
{
    const FOLDER_STATIC_DATA = '/static-data/';
    const FOLDER_CACHE_TOP_DATA = '/cache-top-data/';
    const FOLDER_ERRORS = '/errors/';
    const FOLDER_REQUESTS = '/requests/';
    const FOLDER_BOOKINGS = '/bookings/';

    protected function writeLog(string $level, string $message, array $context): void
    {
        $dir = __DIR__.'/../../storage/logs';
        $date = (new DateTime())->format('Y-m-d H:i:s');
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        if ($level === LogLevel::INFO) {
            if (isset($context['handle'])) {
                if (!isset($context['folder'])) {
                    Log::error('Logging folder not specified!');
                }

                if (!is_dir($dir . $context['folder'] . $context['handle'])) {
                    mkdir($dir . $context['folder'] . $context['handle'], 0755, true);
                }
                file_put_contents($dir . $context['folder'] . $context['handle'] . '/info.txt', $date . '[' . strtoupper($level) . ']: ' . $message . PHP_EOL . PHP_EOL, FILE_APPEND);
            } else {
                file_put_contents($dir . '/info.txt', $date . '[' . strtoupper($level) . ']: ' . $message . PHP_EOL . PHP_EOL, FILE_APPEND);
            }
        } elseif ($level === LogLevel::ERROR) {
            if (isset($context['handle'])) {
                if (!isset($context['folder'])) {
                    Log::error('Logging folder not specified!');
                }

                if (!is_dir($dir . $context['folder'] . $context['handle'])) {
                    mkdir($dir . $context['folder'] . $context['handle'], 0755, true);
                }
                file_put_contents($dir . $context['folder'] . $context['handle'] . '/error.txt', $date . '[' . strtoupper($level) . ']: ' . $message . PHP_EOL . PHP_EOL, FILE_APPEND);
            } else {
                file_put_contents($dir . '/error.txt', $date . '[' . strtoupper($level) . ']: ' . $message . PHP_EOL . PHP_EOL, FILE_APPEND);
            }
        } elseif ($level === LogLevel::DEBUG){
            file_put_contents($dir . '/debug.txt', $date . '[' . strtoupper($level) . ']: ' . $message . PHP_EOL . PHP_EOL, FILE_APPEND);
        } elseif ($level === LogLevel::WARNING) {
            file_put_contents($dir . '/warning.txt', $date . '[' . strtoupper($level) . ']: ' . $message . PHP_EOL . PHP_EOL, FILE_APPEND);
        } else {
            file_put_contents($dir . '/log.txt', $date . '[' . strtoupper($level) . ']: ' . $message . PHP_EOL . PHP_EOL, FILE_APPEND);
        }
    }

	public function emergency(string $message, array $context = []): void
    {
        $this->writeLog(LogLevel::EMERGENCY, $message, $context);
	}
	
	public function alert(string $message, array $context = []): void
    {
        $this->writeLog(LogLevel::ALERT, $message, $context);
	}
	
	public function critical(string $message, array $context = []): void
    {
        $this->writeLog(LogLevel::CRITICAL, $message, $context);
	}
	
	public function error(string $message, array $context = []): void
    {
        $this->writeLog(LogLevel::ERROR, $message, $context);
	}
	
	public function warning(string $message, array $context = []): void
    {
        $this->writeLog(LogLevel::WARNING, $message, $context);
	}
	
	public function notice(string $message, array $context = []): void
    {
        $this->writeLog(LogLevel::NOTICE, $message, $context);
	}
	
	public function info(string $message, array $context = []): void
    {
        $this->writeLog(LogLevel::INFO, $message, $context);
	}
	
	public function debug(string $message, array $context = []): void
    {
        $this->writeLog(LogLevel::DEBUG, $message, $context);
	}
	
	public function log(mixed $level, string $message, array $context = []): void
    {
        $this->writeLog($level, $message, $context);
	}
}