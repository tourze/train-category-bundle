<?php

namespace Tourze\TrainCategoryBundle\Tests\Unit\Exception;

use PHPUnit\Framework\TestCase;
use Tourze\TrainCategoryBundle\Exception\CategoryDeletionException;

class CategoryDeletionExceptionTest extends TestCase
{
    public function testDefaultMessage(): void
    {
        $exception = new CategoryDeletionException();
        
        $this->assertEquals('无法删除分类', $exception->getMessage());
        $this->assertEquals(0, $exception->getCode());
        $this->assertInstanceOf(\InvalidArgumentException::class, $exception);
    }

    public function testCustomMessage(): void
    {
        $exception = new CategoryDeletionException('自定义错误信息', 123);
        
        $this->assertEquals('自定义错误信息', $exception->getMessage());
        $this->assertEquals(123, $exception->getCode());
    }
}