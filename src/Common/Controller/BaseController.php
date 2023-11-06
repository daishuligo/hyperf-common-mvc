<?php

namespace YueChuan\Common\Controller;

use Hyperf\Di\Annotation\Inject;
use Psr\Http\Message\ResponseInterface;
use YueChuan\Common\Request\BaseRequest;
use YueChuan\Common\Response\BaseResponse;
use YueChuan\Utils\LogUtil;

abstract class BaseController
{
    #[Inject]
    protected BaseRequest $request;

    #[Inject]
    protected BaseResponse $response;

    public function success(string|array|object $msgOrData = '', array|object $data = [], int $code = 200): ResponseInterface
    {
        if (is_string($msgOrData) || is_null($msgOrData)) {
            return $this->response->success($msgOrData, $data, $code);
        } else if (is_array($msgOrData) || is_object($msgOrData)) {
            return $this->response->success(null, $msgOrData, $code);
        } else {
            return $this->response->success(null, $data, $code);
        }
    }

    public function error(string $message = '', int $code = 500, array $data = []): ResponseInterface
    {
        return $this->response->error($message, $code, $data);
    }
}