<?php

namespace YueChuan\Utils;

use RuntimeException;
use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\StdoutLoggerInterface;
use Psr\Container\ContainerInterface;
use YueChuan\Common\Request\BaseRequest;

class CommonUtil
{
    public static function container(): ContainerInterface
    {
        return ApplicationContext::getContainer();
    }

    public static function getLanguage(): ?string
    {
        $acceptLanguage = null;
        try {
            $acceptLanguage = self::container()->get(BaseRequest::class)->getHeaderLine('accept-language');
        } catch (\Throwable $e) {
            LogUtil::getLogger()->debug("[语言]获取header传输指定语言失败：" . $e->getMessage());
        }
        return !empty($acceptLanguage) ? explode(',', $acceptLanguage)[0] : 'zh_CN';
    }

    public static function trans(string $key, $replace = []): string
    {
        return \Hyperf\Translation\trans($key, $replace, self::getLanguage());
    }

    public static function console()
    {
        try {
            return self::container()->get(StdoutLoggerInterface::class);
        } catch (\Throwable $e) {
            throw new RuntimeException($e->getMessage());
        }
    }
}