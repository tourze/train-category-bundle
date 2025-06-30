<?php

namespace Tourze\TrainCategoryBundle\Tests\Unit\Procedure;

use PHPUnit\Framework\TestCase;
use Tourze\JsonRPC\Core\Exception\ApiException;
use Tourze\TrainCategoryBundle\Entity\Category;
use Tourze\TrainCategoryBundle\Procedure\GetJobTrainingCategory;
use Tourze\TrainCategoryBundle\Repository\CategoryRepository;

class GetJobTrainingCategoryTest extends TestCase
{
    private CategoryRepository $categoryRepository;
    private GetJobTrainingCategory $procedure;

    protected function setUp(): void
    {
        $this->categoryRepository = $this->createMock(CategoryRepository::class);
        $this->procedure = new GetJobTrainingCategory($this->categoryRepository);
    }

    public function testExecuteWithoutParentId(): void
    {
        $category = $this->createMock(Category::class);
        $category->method('retrieveApiArray')->willReturn([
            'id' => 1,
            'title' => 'Test Category',
        ]);

        $this->categoryRepository
            ->expects($this->once())
            ->method('findBy')
            ->with(['parent' => null], ['sortNumber' => 'DESC', 'id' => 'ASC'])
            ->willReturn([$category]);

        $result = $this->procedure->execute();

        $this->assertCount(1, $result);
        $this->assertEquals([
            [
                'id' => 1,
                'title' => 'Test Category',
            ]
        ], $result);
    }

    public function testExecuteWithValidParentId(): void
    {
        $this->procedure->parentId = '1';

        $parent = $this->createMock(Category::class);
        $child = $this->createMock(Category::class);
        $child->method('retrieveApiArray')->willReturn([
            'id' => 2,
            'title' => 'Child Category',
        ]);

        $this->categoryRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['id' => '1'])
            ->willReturn($parent);

        $this->categoryRepository
            ->expects($this->once())
            ->method('findBy')
            ->with(['parent' => $parent], ['sortNumber' => 'DESC', 'id' => 'ASC'])
            ->willReturn([$child]);

        $result = $this->procedure->execute();

        $this->assertCount(1, $result);
        $this->assertEquals([
            [
                'id' => 2,
                'title' => 'Child Category',
            ]
        ], $result);
    }

    public function testExecuteWithInvalidParentId(): void
    {
        $this->procedure->parentId = '999';

        $this->categoryRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['id' => '999'])
            ->willReturn(null);

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('找不到上级目录');

        $this->procedure->execute();
    }
}