<?php

namespace YueChuan\Utils;

use Hyperf\Logger\LoggerFactory;
use Psr\Log\LoggerInterface;

class LogUtil
{
    private static LoggerFactory $loggerFactory;

    public static function getLogger($logName = 'log', $drive = 'default'): LoggerInterface
    {
        return self::$loggerFactory->get($logName, $drive);
    }
}