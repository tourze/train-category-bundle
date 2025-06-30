<?php

namespace Tourze\TrainCategoryBundle\Exception;

/**
 * 分类移动异常
 */
class CategoryMoveException extends \InvalidArgumentException
{
    public function __construct(string $message = '无法移动分类', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}