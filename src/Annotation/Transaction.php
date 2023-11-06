<?php

namespace YueChuan\Annotation;

use Attribute;
use Hyperf\Di\Annotation\AbstractAnnotation;

#[Attribute(Attribute::TARGET_METHOD)]
class Transaction extends AbstractAnnotation
{
    public int $retry = 1;

    public string $connection = 'default';

    public function __construct($connection = 'default', $value = 1)
    {
        $this->retry = $value;
        $this->connection = $connection;
    }
}