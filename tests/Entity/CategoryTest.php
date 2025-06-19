<?php

namespace Tourze\TrainCategoryBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use Tourze\TrainCategoryBundle\Entity\Category;

class CategoryTest extends TestCase
{
    private Category $category;

    protected function setUp(): void
    {
        $this->category = new Category();
    }

    public function test_setTitle_withValidTitle(): void
    {
        $title = '测试分类';
        $this->category->setTitle($title);
        
        $this->assertEquals($title, $this->category->getTitle());
    }

    public function test_setSortNumber_withValidNumber(): void
    {
        $sortNumber = 100;
        $this->category->setSortNumber($sortNumber);
        
        $this->assertEquals($sortNumber, $this->category->getSortNumber());
    }

    public function test_setSortNumber_withNull(): void
    {
        $this->category->setSortNumber(null);
        
        $this->assertNull($this->category->getSortNumber());
    }

    public function test_setParent_withValidParent(): void
    {
        $parent = new Category();
        $parent->setTitle('父分类');
        
        $this->category->setParent($parent);
        
        $this->assertSame($parent, $this->category->getParent());
    }

    public function test_setParent_withNull(): void
    {
        $this->category->setParent(null);
        
        $this->assertNull($this->category->getParent());
    }

    public function test_addChild_withValidChild(): void
    {
        $child = new Category();
        $child->setTitle('子分类');
        
        $this->category->addChild($child);
        
        $this->assertTrue($this->category->getChildren()->contains($child));
        $this->assertSame($this->category, $child->getParent());
    }

    public function test_addChild_withDuplicateChild(): void
    {
        $child = new Category();
        $child->setTitle('子分类');
        
        $this->category->addChild($child);
        $this->category->addChild($child); // 重复添加
        
        $this->assertEquals(1, $this->category->getChildren()->count());
    }

    public function test_removeChild_withExistingChild(): void
    {
        $child = new Category();
        $child->setTitle('子分类');
        
        $this->category->addChild($child);
        $this->category->removeChild($child);
        
        $this->assertFalse($this->category->getChildren()->contains($child));
        $this->assertNull($child->getParent());
    }

    public function test_removeChild_withNonExistingChild(): void
    {
        $child = new Category();
        $child->setTitle('子分类');
        
        $this->category->removeChild($child); // 移除不存在的子分类
        
        $this->assertEquals(0, $this->category->getChildren()->count());
    }

    public function test_toString_withoutParent(): void
    {
        $this->category->setTitle('根分类');
        
        // 模拟有ID的情况
        $reflection = new \ReflectionClass($this->category);
        $idProperty = $reflection->getProperty('id');
        $idProperty->setAccessible(true);
        $idProperty->setValue($this->category, '123456789');
        
        $this->assertEquals('根分类', (string) $this->category);
    }

    public function test_toString_withParent(): void
    {
        $parent = new Category();
        $parent->setTitle('父分类');
        
        // 模拟父分类有ID
        $reflection = new \ReflectionClass($parent);
        $idProperty = $reflection->getProperty('id');
        $idProperty->setAccessible(true);
        $idProperty->setValue($parent, '123456788');
        
        $this->category->setTitle('子分类');
        $this->category->setParent($parent);
        
        // 模拟子分类有ID
        $reflection = new \ReflectionClass($this->category);
        $idProperty = $reflection->getProperty('id');
        $idProperty->setAccessible(true);
        $idProperty->setValue($this->category, '123456789');
        
        $this->assertEquals('父分类/子分类', (string) $this->category);
    }

    public function test_toString_withoutId(): void
    {
        $this->category->setTitle('测试分类');
        
        $this->assertEquals('', (string) $this->category);
    }

    public function test_getChildren_initiallyEmpty(): void
    {
        $this->assertEquals(0, $this->category->getChildren()->count());
    }

    public function test_setCreatedBy_withValidUser(): void
    {
        $user = 'test_user';
        $this->category->setCreatedBy($user);
        
        $this->assertEquals($user, $this->category->getCreatedBy());
    }

    public function test_setUpdatedBy_withValidUser(): void
    {
        $user = 'test_user';
        $this->category->setUpdatedBy($user);
        
        $this->assertEquals($user, $this->category->getUpdatedBy());
    }

    public function test_setCreateTime_withValidDateTime(): void
    {
        $dateTime = new \DateTimeImmutable();
        $this->category->setCreateTime($dateTime);
        
        $this->assertSame($dateTime, $this->category->getCreateTime());
    }

    public function test_setUpdateTime_withValidDateTime(): void
    {
        $dateTime = new \DateTimeImmutable();
        $this->category->setUpdateTime($dateTime);
        
        $this->assertSame($dateTime, $this->category->getUpdateTime());
    }

    public function test_retrieveApiArray_withBasicData(): void
    {
        $this->category->setTitle('API测试分类');
        $this->category->setSortNumber(50);
        
        $apiArray = $this->category->retrieveApiArray();
        
        $this->assertArrayHasKey('id', $apiArray);
        $this->assertArrayHasKey('title', $apiArray);
        $this->assertEquals('API测试分类', $apiArray['title']);
    }

    public function test_retrieveAdminArray_withBasicData(): void
    {
        $this->category->setTitle('管理测试分类');
        $this->category->setSortNumber(75);
        
        $adminArray = $this->category->retrieveAdminArray();
        
        $this->assertArrayHasKey('id', $adminArray);
        $this->assertArrayHasKey('title', $adminArray);
        $this->assertEquals('管理测试分类', $adminArray['title']);
    }
} 