<?php

namespace YueChuan\Common\Request;

use Hyperf\Collection\Arr;
use Hyperf\HttpServer\Request;

class BaseRequest extends Request
{
    public function only(array $keys): array
    {
        return Arr::only($this->all(), $keys);
    }

    public function ip(): string
    {
        $ip = $this->getServerParams()['remote_addr'] ?? '0.0.0.0';
        $headers = $this->getHeaders();

        if (isset($headers['x-real-ip'])){
            $ip = $headers['x-real-ip'][0];
        }elseif (isset($headers['x-forwarded-for'])){
            $ip = $headers['x-forwarded-for'][0];
        }elseif (isset($headers['http_x_forwarded_for'])){
            $ip = $headers['http_x_forwarded_for'];
        }

        return $ip;
    }

    public function setProperty(string $name, mixed $value): static
    {
        parent:$this->storeRequestProperty($name, $value);
        return $this;
    }

    public function getProperty(string $name): mixed
    {
        return parent::getRequestProperty($name);
    }
}