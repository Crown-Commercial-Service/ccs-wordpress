<?php

namespace App\Services\Logger;

use Monolog\Handler\StreamHandler;

/**
 * Class AppLogger
 * @package App\Services\Logger
 */
class AppLogger extends \Monolog\Logger
{

    /**
     * AppLogger constructor.
     * @param array $handlers
     * @param array $processors
     * @param \DateTimeZone|null $timezone
     * @throws \Exception
     */
    public function __construct($handlers = [], $processors = [], ?\DateTimeZone $timezone = null)
    {

        parent::__construct('app', $handlers, $processors, $timezone);

        $logFileLocation = __DIR__ . '/../../../../var/log/app.log';

        if (!file_exists($logFileLocation)) {
            touch($logFileLocation);
        }

        $this->pushHandler(new StreamHandler($logFileLocation, ImportLogger::DEBUG));
    }
}
