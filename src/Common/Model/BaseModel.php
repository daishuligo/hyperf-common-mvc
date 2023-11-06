<?php

namespace YueChuan\Common\Model;

use Hyperf\DbConnection\Model\Model;
abstract class BaseModel extends Model
{
    public const PAGE_SIZE = 15;

    public const SOFT_DELETED = 1;

    public const SOFT_NO_DELETED = 0;

    private string $softDeleteKey = '';

    public function getSoftDeleteKey(): string
    {
        return $this->softDeleteKey;
    }
}