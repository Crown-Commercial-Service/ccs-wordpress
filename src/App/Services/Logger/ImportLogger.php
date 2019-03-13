<?php

namespace App\Services\Logger;

use Monolog\Handler\StreamHandler;

/**
 * Class ImportLogger
 * @package App\Services\Logger
 */
class ImportLogger extends \Monolog\Logger
{
    /**
     * ImportLogger constructor.
     * @param array $handlers
     * @param array $processors
     * @param \DateTimeZone|null $timezone
     * @throws \Exception
     */
    public function __construct($handlers = [], $processors = [], ?\DateTimeZone $timezone = null) {

        parent::__construct('import', $handlers, $processors, $timezone);

        $logFileLocation = __DIR__ . '/../../../../var/log/import.log';

        if (!file_exists($logFileLocation))
        {
            touch($logFileLocation);
        }

        $this->pushHandler(new StreamHandler($logFileLocation, ImportLogger::DEBUG));
    }


}