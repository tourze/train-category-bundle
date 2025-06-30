<?php

namespace Tourze\TrainCategoryBundle\Exception;

/**
 * 分类删除异常
 */
class CategoryDeletionException extends \InvalidArgumentException
{
    public function __construct(string $message = '无法删除分类', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}