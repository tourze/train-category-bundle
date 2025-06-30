<?php

namespace Tourze\TrainCategoryBundle\Tests\Unit\Exception;

use PHPUnit\Framework\TestCase;
use Tourze\TrainCategoryBundle\Exception\CategoryRequirementValidationException;

class CategoryRequirementValidationExceptionTest extends TestCase
{
    public function testDefaultMessage(): void
    {
        $exception = new CategoryRequirementValidationException();
        
        $this->assertEquals('培训要求配置不合理', $exception->getMessage());
        $this->assertEquals(0, $exception->getCode());
        $this->assertInstanceOf(\InvalidArgumentException::class, $exception);
    }

    public function testCustomMessage(): void
    {
        $exception = new CategoryRequirementValidationException('验证失败', 789);
        
        $this->assertEquals('验证失败', $exception->getMessage());
        $this->assertEquals(789, $exception->getCode());
    }
}