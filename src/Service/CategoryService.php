<?php

namespace Tourze\TrainCategoryBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Tourze\TrainCategoryBundle\Entity\Category;
use Tourze\TrainCategoryBundle\Repository\CategoryRepository;

/**
 * 分类服务类
 *
 * 提供分类管理的核心业务逻辑，包括CRUD操作、树形结构管理、查询方法等
 */
class CategoryService
{
    public function __construct(
        private readonly CategoryRepository $categoryRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * 创建新分类
     */
    public function createCategory(string $title, ?Category $parent = null, int $sortNumber = 0): Category
    {
        $category = new Category();
        $category->setTitle($title);
        $category->setParent($parent);
        $category->setSortNumber($sortNumber);

        $this->entityManager->persist($category);
        $this->entityManager->flush();

        return $category;
    }

    /**
     * 更新分类信息
     * @param array<string, mixed> $data
     */
    public function updateCategory(Category $category, array $data): Category
    {
        if (isset($data['title']) && is_string($data['title'])) {
            $category->setTitle($data['title']);
        }

        if (array_key_exists('parent', $data)) {
            $parent = $data['parent'];
            if ($parent instanceof Category || $parent === null) {
                $category->setParent($parent);
            }
        }

        if (isset($data['sortNumber']) && is_int($data['sortNumber'])) {
            $category->setSortNumber($data['sortNumber']);
        }

        $this->entityManager->flush();

        return $category;
    }

    /**
     * 删除分类
     *
     * 注意：删除前会检查是否有子分类
     */
    public function deleteCategory(Category $category): void
    {
        // 检查是否有子分类
        if ($category->getChildren()->count() > 0) {
            throw new \InvalidArgumentException('无法删除包含子分类的分类');
        }

        $this->entityManager->remove($category);
        $this->entityManager->flush();
    }

    /**
     * 获取分类树形结构
     * @return array<int, array<string, mixed>>
     */
    public function getCategoryTree(?Category $root = null): array
    {
        if ($root === null) {
            $rootCategories = $this->findRootCategories();
            return array_map([$this, 'buildCategoryTree'], $rootCategories);
        }

        return [$this->buildCategoryTree($root)];
    }

    /**
     * 构建单个分类的树形结构
     * @return array<string, mixed>
     */
    private function buildCategoryTree(Category $category): array
    {
        $tree = [
            'id' => $category->getId(),
            'title' => $category->getTitle(),
            'sortNumber' => $category->getSortNumber(),
            'children' => [],
        ];

        foreach ($category->getChildren() as $child) {
            $tree['children'][] = $this->buildCategoryTree($child);
        }

        // 按排序值排序子分类
        usort($tree['children'], function ($a, $b) {
            return $b['sortNumber'] <=> $a['sortNumber'];
        });

        return $tree;
    }

    /**
     * 获取分类路径
     * @return array<int, Category>
     */
    public function getCategoryPath(Category $category): array
    {
        $path = [];
        $current = $category;

        while ($current !== null) {
            array_unshift($path, $current);
            $current = $current->getParent();
        }

        return $path;
    }

    /**
     * 移动分类到新的父级
     */
    public function moveCategoryTo(Category $category, ?Category $newParent): void
    {
        // 检查是否会形成循环引用
        if ($newParent !== null && $this->wouldCreateCircularReference($category, $newParent)) {
            throw new \InvalidArgumentException('无法移动分类：会形成循环引用');
        }

        $category->setParent($newParent);
        $this->entityManager->flush();
    }

    /**
     * 检查是否会形成循环引用
     */
    private function wouldCreateCircularReference(Category $category, Category $newParent): bool
    {
        $current = $newParent;
        while ($current !== null) {
            if ($current->getId() === $category->getId()) {
                return true;
            }
            $current = $current->getParent();
        }
        return false;
    }

    /**
     * 查找指定层级的分类
     * @return array<int, Category>
     */
    public function findByLevel(int $level): array
    {
        $qb = $this->categoryRepository->createQueryBuilder('c');
        
        // 构建查询条件
        for ($i = 0; $i < $level; $i++) {
            if ($i === 0) {
                $qb->leftJoin('c.parent', 'p0');
            } else {
                $prevAlias = 'p' . ($i - 1);
                $currentAlias = 'p' . $i;
                $qb->leftJoin($prevAlias . '.parent', $currentAlias);
            }
        }

        if ($level === 1) {
            $qb->where('c.parent IS NULL');
        } else {
            $prevAlias = 'p' . ($level - 2);
            $currentAlias = 'p' . ($level - 1);
            $qb->where($prevAlias . ' IS NOT NULL')
               ->andWhere($currentAlias . ' IS NULL');
        }

        $qb->orderBy('c.sortNumber', 'DESC')
           ->addOrderBy('c.id', 'DESC');

        /** @var array<int, Category> */
        return $qb->getQuery()->getResult();
    }

    /**
     * 查找根分类（顶级分类）
     * @return array<int, Category>
     */
    public function findRootCategories(): array
    {
        return $this->categoryRepository->findBy(
            ['parent' => null],
            ['sortNumber' => 'DESC', 'id' => 'DESC']
        );
    }

    /**
     * 查找叶子分类（没有子分类的分类）
     * @return array<int, Category>
     */
    public function findLeafCategories(): array
    {
        $qb = $this->categoryRepository->createQueryBuilder('c');
        $qb->leftJoin('c.children', 'children')
           ->where('children.id IS NULL')
           ->orderBy('c.sortNumber', 'DESC')
           ->addOrderBy('c.id', 'DESC');

        /** @var array<int, Category> */
        return $qb->getQuery()->getResult();
    }

    /**
     * 获取标准化分类
     *
     * 根据AQ8011-2023标准返回预定义的分类结构
     * @return array<string, array<int, string>>
     */
    public function getStandardizedCategories(): array
    {
        return [
            '培训类别' => [
                '特种作业人员培训',
                '生产经营单位主要负责人培训',
                '安全生产管理人员培训',
                '其他从业人员培训',
            ],
            '行业分类' => [
                '矿山行业',
                '危险化学品行业',
                '石油天然气开采行业',
                '金属冶炼行业',
                '建筑施工行业',
                '道路运输行业',
                '其他行业',
            ],
            '特种作业类别' => [
                '电工作业',
                '焊接与热切割作业',
                '高处作业',
                '制冷与空调作业',
                '煤矿安全作业',
                '金属非金属矿山安全作业',
                '石油天然气安全作业',
                '冶金（有色）生产安全作业',
                '危险化学品安全作业',
                '烟花爆竹安全作业',
            ],
        ];
    }

    /**
     * 按类型获取分类
     * @return array<int, Category>
     */
    public function getCategoryByType(string $type): array
    {
        // 这里可以根据分类的命名规则或者额外的类型字段来筛选
        // 目前基于分类名称进行简单匹配
        $allCategories = $this->categoryRepository->findAll();

        return array_filter($allCategories, function (Category $category) use ($type) {
            return str_contains($category->getTitle(), $type);
        });
    }

    /**
     * 验证分类结构
     * @return array<int, string>
     */
    public function validateCategoryStructure(Category $category): array
    {
        $errors = [];

        // 检查标题是否为空
        if (empty($category->getTitle())) {
            $errors[] = '分类名称不能为空';
        }

        // 检查标题长度
        if (strlen($category->getTitle()) > 100) {
            $errors[] = '分类名称不能超过100个字符';
        }

        // 检查是否存在同名的兄弟分类
        $siblings = $this->findSiblingCategories($category);
        foreach ($siblings as $sibling) {
            if ($sibling->getId() !== $category->getId() && $sibling->getTitle() === $category->getTitle()) {
                $errors[] = '同级分类中已存在相同名称的分类';
                break;
            }
        }

        return $errors;
    }

    /**
     * 查找兄弟分类
     * @return array<int, Category>
     */
    private function findSiblingCategories(Category $category): array
    {
        return $this->categoryRepository->findBy(['parent' => $category->getParent()]);
    }

    /**
     * 导入标准分类
     */
    public function importStandardCategories(array $categories): void
    {
        foreach ($categories as $parentTitle => $children) {
            // 创建或查找父分类
            $parent = $this->categoryRepository->findOneBy(['title' => $parentTitle, 'parent' => null]);
            if ($parent === null) {
                $parent = $this->createCategory($parentTitle);
            }

            // 创建子分类
            foreach ($children as $index => $childTitle) {
                $child = $this->categoryRepository->findOneBy(['title' => $childTitle, 'parent' => $parent]);
                if ($child === null) {
                    $this->createCategory($childTitle, $parent, 1000 - $index);
                }
            }
        }
    }

    /**
     * 获取默认分类
     */
    public function getDefaultCategory(): Category
    {
        return $this->categoryRepository->getDefaultCategory();
    }

    /**
     * 根据标题查找分类
     */
    public function findByTitle(string $title): ?Category
    {
        return $this->categoryRepository->findOneBy(['title' => $title]);
    }

    /**
     * 根据标题和父分类查找分类
     */
    public function findByTitleAndParent(string $title, ?Category $parent): ?Category
    {
        return $this->categoryRepository->findOneBy([
            'title' => $title,
            'parent' => $parent
        ]);
    }
}
