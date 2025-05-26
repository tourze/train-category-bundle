<?php

namespace Tourze\TrainCategoryBundle\Service;

use Knp\Menu\ItemInterface;
use Tourze\EasyAdminMenuBundle\Service\LinkGeneratorInterface;
use Tourze\EasyAdminMenuBundle\Service\MenuProviderInterface;
use Tourze\TrainCategoryBundle\Entity\Category;
use Tourze\TrainCategoryBundle\Entity\CategoryRequirement;

/**
 * 培训分类管理菜单服务
 */
class AdminMenu implements MenuProviderInterface
{
    public function __construct(
        private readonly LinkGeneratorInterface $linkGenerator,
    ) {
    }

    public function __invoke(ItemInterface $item): void
    {
        // 创建主菜单项 培训分类管理
        $categoryMenu = $item->getChild('培训分类管理');
        if (!$categoryMenu) {
            $categoryMenu = $item->addChild('培训分类管理')
                ->setAttribute('icon', 'fa fa-sitemap');
        }

        $categoryMenu->addChild('分类管理')->setUri($this->linkGenerator->getCurdListPage(Category::class))->setAttribute('icon', 'fa fa-folder-tree');
        $categoryMenu->addChild('培训要求')->setUri($this->linkGenerator->getCurdListPage(CategoryRequirement::class))->setAttribute('icon', 'fa fa-clipboard-list');
    }
}
