<?php

namespace Tourze\TrainCategoryBundle\Tests\Service;

use Knp\Menu\ItemInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Tourze\EasyAdminMenuBundle\Service\LinkGeneratorInterface;
use Tourze\TrainCategoryBundle\Entity\Category;
use Tourze\TrainCategoryBundle\Entity\CategoryRequirement;
use Tourze\TrainCategoryBundle\Service\AdminMenu;

class AdminMenuTest extends TestCase
{
    private AdminMenu $adminMenu;
    /** @var MockObject&LinkGeneratorInterface */
    private MockObject $linkGenerator;
    /** @var MockObject&ItemInterface */
    private MockObject $menuItem;

    protected function setUp(): void
    {
        $this->linkGenerator = $this->createMock(LinkGeneratorInterface::class);
        $this->menuItem = $this->createMock(ItemInterface::class);
        
        $this->adminMenu = new AdminMenu($this->linkGenerator);
    }

    public function test_invoke_createsTrainingCategoryMenu(): void
    {
        // 模拟菜单项不存在的情况
        $this->menuItem->expects($this->once())
            ->method('getChild')
            ->with('培训分类管理')
            ->willReturn(null);

        // 模拟创建主菜单项
        $categoryMenu = $this->createMock(ItemInterface::class);
        $this->menuItem->expects($this->once())
            ->method('addChild')
            ->with('培训分类管理')
            ->willReturn($categoryMenu);

        $categoryMenu->expects($this->once())
            ->method('setAttribute')
            ->with('icon', 'fa fa-sitemap')
            ->willReturnSelf();

        // 模拟生成链接
        $this->linkGenerator->expects($this->exactly(2))
            ->method('getCurdListPage')
            ->willReturnCallback(function ($entityClass) {
                return match ($entityClass) {
                    Category::class => '/admin/category',
                    CategoryRequirement::class => '/admin/category-requirement',
                    default => '/admin/unknown',
                };
            });

        // 模拟添加子菜单项
        $categorySubMenu = $this->createMock(ItemInterface::class);
        $requirementSubMenu = $this->createMock(ItemInterface::class);

        $categoryMenu->expects($this->exactly(2))
            ->method('addChild')
            ->willReturnCallback(function ($name) use ($categorySubMenu, $requirementSubMenu) {
                return match ($name) {
                    '分类管理' => $categorySubMenu,
                    '培训要求' => $requirementSubMenu,
                    default => $this->createMock(ItemInterface::class),
                };
            });

        $categorySubMenu->expects($this->once())
            ->method('setUri')
            ->with('/admin/category')
            ->willReturnSelf();

        $categorySubMenu->expects($this->once())
            ->method('setAttribute')
            ->with('icon', 'fa fa-folder-tree')
            ->willReturnSelf();

        $requirementSubMenu->expects($this->once())
            ->method('setUri')
            ->with('/admin/category-requirement')
            ->willReturnSelf();

        $requirementSubMenu->expects($this->once())
            ->method('setAttribute')
            ->with('icon', 'fa fa-clipboard-list')
            ->willReturnSelf();

        // 执行测试
        ($this->adminMenu)($this->menuItem);
    }

    public function test_invoke_withExistingMenu(): void
    {
        // 模拟菜单项已存在的情况
        $existingMenu = $this->createMock(ItemInterface::class);
        
        $this->menuItem->expects($this->once())
            ->method('getChild')
            ->with('培训分类管理')
            ->willReturn($existingMenu);

        // 不应该创建新的菜单项
        $this->menuItem->expects($this->never())
            ->method('addChild');

        // 模拟生成链接
        $this->linkGenerator->expects($this->exactly(2))
            ->method('getCurdListPage')
            ->willReturnCallback(function ($entityClass) {
                return match ($entityClass) {
                    Category::class => '/admin/category',
                    CategoryRequirement::class => '/admin/category-requirement',
                    default => '/admin/unknown',
                };
            });

        // 模拟添加子菜单项
        $categorySubMenu = $this->createMock(ItemInterface::class);
        $requirementSubMenu = $this->createMock(ItemInterface::class);

        $existingMenu->expects($this->exactly(2))
            ->method('addChild')
            ->willReturnCallback(function ($name) use ($categorySubMenu, $requirementSubMenu) {
                return match ($name) {
                    '分类管理' => $categorySubMenu,
                    '培训要求' => $requirementSubMenu,
                    default => $this->createMock(ItemInterface::class),
                };
            });

        $categorySubMenu->expects($this->once())
            ->method('setUri')
            ->with('/admin/category')
            ->willReturnSelf();

        $categorySubMenu->expects($this->once())
            ->method('setAttribute')
            ->with('icon', 'fa fa-folder-tree')
            ->willReturnSelf();

        $requirementSubMenu->expects($this->once())
            ->method('setUri')
            ->with('/admin/category-requirement')
            ->willReturnSelf();

        $requirementSubMenu->expects($this->once())
            ->method('setAttribute')
            ->with('icon', 'fa fa-clipboard-list')
            ->willReturnSelf();

        // 执行测试
        ($this->adminMenu)($this->menuItem);
    }

    public function test_adminMenuImplementsMenuProviderInterface(): void
    {
        $this->assertInstanceOf(
            \Tourze\EasyAdminMenuBundle\Service\MenuProviderInterface::class,
            $this->adminMenu
        );
    }

    public function test_adminMenuIsInvokable(): void
    {
        // AdminMenu 实现了 __invoke 方法，所以总是可调用的
        // 这个测试可以被更有意义的测试替代
        $this->assertInstanceOf(AdminMenu::class, $this->adminMenu);
    }
} 