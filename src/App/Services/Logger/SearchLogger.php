<?php

namespace App\Services\Logger;

use Monolog\Handler\StreamHandler;

/**
 * Class AppLogger
 * @package App\Services\Logger
 */
class SearchLogger extends \Monolog\Logger
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

        parent::__construct('search', $handlers, $processors, $timezone);

        $logFileLocation = __DIR__ . '/../../../../var/log/search.log';

        if (!file_exists($logFileLocation)) {
            touch($logFileLocation);
        }

        $this->pushHandler(new StreamHandler($logFileLocation, ImportLogger::ERROR));
    }
}
