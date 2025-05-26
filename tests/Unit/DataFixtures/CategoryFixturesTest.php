<?php

namespace Tourze\TrainCategoryBundle\Tests\Unit\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Doctrine\Persistence\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Tourze\TrainCategoryBundle\DataFixtures\CategoryFixtures;
use Tourze\TrainCategoryBundle\Entity\Category;

/**
 * CategoryFixtures 测试
 */
class CategoryFixturesTest extends TestCase
{
    private ObjectManager&MockObject $objectManager;
    private CategoryFixtures $fixture;

    protected function setUp(): void
    {
        $this->objectManager = $this->createMock(ObjectManager::class);
        $this->fixture = new CategoryFixtures();
    }

    public function testIsFixture(): void
    {
        $this->assertInstanceOf(Fixture::class, $this->fixture);
    }

    public function testGetGroups(): void
    {
        $groups = CategoryFixtures::getGroups();
        $this->assertIsArray($groups);
        $this->assertContains('production', $groups);
        $this->assertContains('dev', $groups);
    }

    public function testLoadCreatesMainCategories(): void
    {
        // CategoryFixtures会创建4个主分类 + 各种子分类，总共约28个分类
        // 我们只验证flush被调用一次
        $this->objectManager
            ->expects($this->atLeastOnce())
            ->method('persist')
            ->with($this->isInstanceOf(Category::class));

        $this->objectManager
            ->expects($this->once())
            ->method('flush');

        // 模拟引用管理
        $this->fixture->setReferenceRepository($this->createMockReferenceRepository());

        $this->fixture->load($this->objectManager);
    }

    public function testMainCategoriesHaveCorrectSortNumbers(): void
    {
        $categories = [];
        
        $this->objectManager
            ->expects($this->atLeastOnce())
            ->method('persist')
            ->willReturnCallback(function ($entity) use (&$categories) {
                if ($entity instanceof Category && $entity->getParent() === null) {
                    $categories[] = $entity;
                }
            });

        // 模拟引用管理
        $this->fixture->setReferenceRepository($this->createMockReferenceRepository());

        $this->fixture->load($this->objectManager);

        // 验证排序号
        $sortNumbers = [];
        foreach ($categories as $category) {
            $sortNumbers[$category->getTitle()] = $category->getSortNumber();
        }

        $this->assertEquals(1000, $sortNumbers['主要负责人'] ?? null);
        $this->assertEquals(2000, $sortNumbers['特种作业人员'] ?? null);
        $this->assertEquals(3000, $sortNumbers['安全生产管理人员'] ?? null);
        $this->assertEquals(0, $sortNumbers['未分类'] ?? null);
    }

    public function testMainCategoriesHaveCorrectTitles(): void
    {
        $categories = [];
        
        $this->objectManager
            ->expects($this->atLeastOnce())
            ->method('persist')
            ->willReturnCallback(function ($entity) use (&$categories) {
                if ($entity instanceof Category && $entity->getParent() === null) {
                    $categories[] = $entity;
                }
            });

        // 模拟引用管理
        $this->fixture->setReferenceRepository($this->createMockReferenceRepository());

        $this->fixture->load($this->objectManager);

        $titles = array_map(fn($category) => $category->getTitle(), $categories);
        
        $this->assertContains('主要负责人', $titles);
        $this->assertContains('特种作业人员', $titles);
        $this->assertContains('安全生产管理人员', $titles);
        $this->assertContains('未分类', $titles);
    }

    /**
     * 创建模拟的引用仓库
     */
    private function createMockReferenceRepository(): ReferenceRepository&MockObject
    {
        $referenceRepository = $this->createMock(ReferenceRepository::class);
        
        // 模拟主分类引用
        $mainCategories = [
            CategoryFixtures::MAIN_RESPONSIBLE_PERSON_REFERENCE => $this->createMockCategory('主要负责人'),
            CategoryFixtures::SPECIAL_OPERATION_PERSONNEL_REFERENCE => $this->createMockCategory('特种作业人员'),
            CategoryFixtures::SAFETY_MANAGEMENT_PERSONNEL_REFERENCE => $this->createMockCategory('安全生产管理人员'),
            CategoryFixtures::UNCATEGORIZED_REFERENCE => $this->createMockCategory('未分类'),
        ];

        $referenceRepository
            ->method('getReference')
            ->willReturnCallback(function ($name) use ($mainCategories) {
                return $mainCategories[$name] ?? $this->createMockCategory('Mock Category');
            });

        return $referenceRepository;
    }

    /**
     * 创建模拟的分类实体
     */
    private function createMockCategory(string $title): Category
    {
        $category = new Category();
        $category->setTitle($title);
        return $category;
    }
} 