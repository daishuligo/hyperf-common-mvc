<?php

namespace YueChuan\Aspect;

use Hyperf\DbConnection\Db;
use Hyperf\Di\Annotation\Aspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use YueChuan\Annotation\Transaction;
use YueChuan\Exception\TransactionException;
use YueChuan\Utils\LogUtil;

#[Aspect]
class TransactionAspect
{
    public array  $annotations = [
        Transaction::class
    ];

    public function process(ProceedingJoinPoint $point)
    {
        $transaction = null;
        if (isset($point->getAnnotationMetadata()->method[Transaction::class])) {
            $transaction = $point->getAnnotationMetadata()->method[Transaction::class];
        }

        try {
            $connection = $transaction->connection;

            Db::connection($connection)->beginTransaction();
            $number = 0;
            $retry = intval($transaction->retry);

            do{
                $result = $point->process();
                if (! is_null($result)){
                    break;
                }

                ++$number;
            }while($number < $retry);

            Db::connection($connection)->commit();
        }catch (\Throwable $e){
            Db::connection($connection)->rollBack();

            LogUtil::getLogger()->debug("[sql]数据库事务执行失败：" . $e->getMessage());
            throw new TransactionException($e->getMessage());
        }

        return $result;
    }

}