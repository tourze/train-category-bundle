<?php

namespace Tourze\TrainCategoryBundle\Tests\Unit\Service;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Tourze\TrainCategoryBundle\Entity\Category;
use Tourze\TrainCategoryBundle\Repository\CategoryRepository;
use Tourze\TrainCategoryBundle\Service\CategoryService;

class CategoryServiceTest extends TestCase
{
    private CategoryService $categoryService;
    private MockObject $categoryRepository;
    private MockObject $entityManager;

    protected function setUp(): void
    {
        $this->categoryRepository = $this->createMock(CategoryRepository::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        
        $this->categoryService = new CategoryService(
            $this->categoryRepository,
            $this->entityManager
        );
    }

    public function test_createCategory_withBasicData(): void
    {
        $title = '测试分类';
        $sortNumber = 100;

        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf(Category::class));

        $this->entityManager->expects($this->once())
            ->method('flush');

        $category = $this->categoryService->createCategory($title, null, $sortNumber);

        $this->assertInstanceOf(Category::class, $category);
        $this->assertEquals($title, $category->getTitle());
        $this->assertEquals($sortNumber, $category->getSortNumber());
        $this->assertNull($category->getParent());
    }

    public function test_createCategory_withParent(): void
    {
        $parent = new Category();
        $parent->setTitle('父分类');
        
        $title = '子分类';

        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf(Category::class));

        $this->entityManager->expects($this->once())
            ->method('flush');

        $category = $this->categoryService->createCategory($title, $parent);

        $this->assertEquals($title, $category->getTitle());
        $this->assertSame($parent, $category->getParent());
    }

    public function test_updateCategory_withTitleChange(): void
    {
        $category = new Category();
        $category->setTitle('原标题');
        
        $newTitle = '新标题';
        $data = ['title' => $newTitle];

        $this->entityManager->expects($this->once())
            ->method('flush');

        $updatedCategory = $this->categoryService->updateCategory($category, $data);

        $this->assertEquals($newTitle, $updatedCategory->getTitle());
    }

    public function test_updateCategory_withParentChange(): void
    {
        $category = new Category();
        $newParent = new Category();
        $newParent->setTitle('新父分类');
        
        $data = ['parent' => $newParent];

        $this->entityManager->expects($this->once())
            ->method('flush');

        $updatedCategory = $this->categoryService->updateCategory($category, $data);

        $this->assertSame($newParent, $updatedCategory->getParent());
    }

    public function test_updateCategory_withSortNumberChange(): void
    {
        $category = new Category();
        $newSortNumber = 200;
        
        $data = ['sortNumber' => $newSortNumber];

        $this->entityManager->expects($this->once())
            ->method('flush');

        $updatedCategory = $this->categoryService->updateCategory($category, $data);

        $this->assertEquals($newSortNumber, $updatedCategory->getSortNumber());
    }

    public function test_deleteCategory_withValidCategory(): void
    {
        $category = new Category();
        $category->setTitle('待删除分类');

        $this->entityManager->expects($this->once())
            ->method('remove')
            ->with($category);

        $this->entityManager->expects($this->once())
            ->method('flush');

        $this->categoryService->deleteCategory($category);
    }

    public function test_deleteCategory_withChildren_throwsException(): void
    {
        $category = new Category();
        $child = new Category();
        $category->addChild($child);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('无法删除包含子分类的分类');

        $this->categoryService->deleteCategory($category);
    }

    public function test_findRootCategories(): void
    {
        $rootCategories = [
            $this->createCategoryWithTitle('根分类1'),
            $this->createCategoryWithTitle('根分类2'),
        ];

        $this->categoryRepository->expects($this->once())
            ->method('findBy')
            ->with(
                ['parent' => null],
                ['sortNumber' => 'DESC', 'id' => 'DESC']
            )
            ->willReturn($rootCategories);

        $result = $this->categoryService->findRootCategories();

        $this->assertEquals($rootCategories, $result);
    }

    public function test_getCategoryPath_withSingleCategory(): void
    {
        $category = $this->createCategoryWithTitle('单独分类');

        $path = $this->categoryService->getCategoryPath($category);

        $this->assertCount(1, $path);
        $this->assertSame($category, $path[0]);
    }

    public function test_getCategoryPath_withParentChild(): void
    {
        $parent = $this->createCategoryWithTitle('父分类');
        $child = $this->createCategoryWithTitle('子分类');
        $child->setParent($parent);

        $path = $this->categoryService->getCategoryPath($child);

        $this->assertCount(2, $path);
        $this->assertSame($parent, $path[0]);
        $this->assertSame($child, $path[1]);
    }

    public function test_moveCategoryTo_withValidMove(): void
    {
        $category = $this->createCategoryWithTitle('移动分类');
        $newParent = $this->createCategoryWithTitle('新父分类');

        // 设置不同的ID以避免循环引用检查
        $this->setPrivateProperty($category, 'id', '123');
        $this->setPrivateProperty($newParent, 'id', '456');

        $this->entityManager->expects($this->once())
            ->method('flush');

        $this->categoryService->moveCategoryTo($category, $newParent);

        $this->assertSame($newParent, $category->getParent());
    }

    public function test_moveCategoryTo_withCircularReference_throwsException(): void
    {
        $parent = $this->createCategoryWithTitle('父分类');
        $child = $this->createCategoryWithTitle('子分类');
        $child->setParent($parent);

        // 设置相同的ID来模拟循环引用
        $this->setPrivateProperty($parent, 'id', '123');
        $this->setPrivateProperty($child, 'id', '123');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('无法移动分类：会形成循环引用');

        $this->categoryService->moveCategoryTo($parent, $child);
    }

    public function test_getStandardizedCategories(): void
    {
        $standardCategories = $this->categoryService->getStandardizedCategories();

        $this->assertArrayHasKey('培训类别', $standardCategories);
        $this->assertArrayHasKey('行业分类', $standardCategories);
        $this->assertArrayHasKey('特种作业类别', $standardCategories);
        
        if (isset($standardCategories['培训类别']) && is_array($standardCategories['培训类别'])) {
            $this->assertContains('特种作业人员培训', $standardCategories['培训类别']);
        }
        if (isset($standardCategories['行业分类']) && is_array($standardCategories['行业分类'])) {
            $this->assertContains('矿山行业', $standardCategories['行业分类']);
        }
        if (isset($standardCategories['特种作业类别']) && is_array($standardCategories['特种作业类别'])) {
            $this->assertContains('电工作业', $standardCategories['特种作业类别']);
        }
    }

    public function test_validateCategoryStructure_withValidCategory(): void
    {
        $category = $this->createCategoryWithTitle('有效分类');
        
        $this->categoryRepository->expects($this->once())
            ->method('findBy')
            ->willReturn([]);

        $errors = $this->categoryService->validateCategoryStructure($category);

        $this->assertEmpty($errors);
    }

    public function test_validateCategoryStructure_withEmptyTitle(): void
    {
        $category = new Category();
        $category->setTitle('');

        $errors = $this->categoryService->validateCategoryStructure($category);

        $this->assertContains('分类名称不能为空', $errors);
    }

    public function test_validateCategoryStructure_withLongTitle(): void
    {
        $category = new Category();
        $category->setTitle(str_repeat('a', 101)); // 超过100个字符

        $errors = $this->categoryService->validateCategoryStructure($category);

        $this->assertContains('分类名称不能超过100个字符', $errors);
    }

    public function test_validateCategoryStructure_withDuplicateSiblingTitle(): void
    {
        $category = $this->createCategoryWithTitle('重复名称');
        $this->setPrivateProperty($category, 'id', '123');
        
        $sibling = $this->createCategoryWithTitle('重复名称');
        $this->setPrivateProperty($sibling, 'id', '456');

        $this->categoryRepository->expects($this->once())
            ->method('findBy')
            ->willReturn([$category, $sibling]);

        $errors = $this->categoryService->validateCategoryStructure($category);

        $this->assertContains('同级分类中已存在相同名称的分类', $errors);
    }

    public function test_getDefaultCategory(): void
    {
        $defaultCategory = $this->createCategoryWithTitle('未分类');

        $this->categoryRepository->expects($this->once())
            ->method('getDefaultCategory')
            ->willReturn($defaultCategory);

        $result = $this->categoryService->getDefaultCategory();

        $this->assertSame($defaultCategory, $result);
    }

    private function createCategoryWithTitle(string $title): Category
    {
        $category = new Category();
        $category->setTitle($title);
        return $category;
    }

    private function setPrivateProperty(object $object, string $property, mixed $value): void
    {
        $reflection = new \ReflectionClass($object);
        $prop = $reflection->getProperty($property);
        $prop->setAccessible(true);
        $prop->setValue($object, $value);
    }
} 