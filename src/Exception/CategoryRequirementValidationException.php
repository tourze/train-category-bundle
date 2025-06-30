<?php

namespace Tourze\TrainCategoryBundle\Exception;

/**
 * 分类培训要求验证异常
 */
class CategoryRequirementValidationException extends \InvalidArgumentException
{
    public function __construct(string $message = '培训要求配置不合理', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}