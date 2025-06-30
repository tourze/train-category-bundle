<?php

namespace Tourze\TrainCategoryBundle\Tests\Unit\Exception;

use PHPUnit\Framework\TestCase;
use Tourze\TrainCategoryBundle\Exception\CategoryMoveException;

class CategoryMoveExceptionTest extends TestCase
{
    public function testDefaultMessage(): void
    {
        $exception = new CategoryMoveException();
        
        $this->assertEquals('无法移动分类', $exception->getMessage());
        $this->assertEquals(0, $exception->getCode());
        $this->assertInstanceOf(\InvalidArgumentException::class, $exception);
    }

    public function testCustomMessage(): void
    {
        $exception = new CategoryMoveException('自定义错误信息', 456);
        
        $this->assertEquals('自定义错误信息', $exception->getMessage());
        $this->assertEquals(456, $exception->getCode());
    }
}