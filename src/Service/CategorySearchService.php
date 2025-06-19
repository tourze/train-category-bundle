<?php

namespace Tourze\TrainCategoryBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Tourze\TrainCategoryBundle\Entity\Category;
use Tourze\TrainCategoryBundle\Repository\CategoryRepository;
use Tourze\TrainCategoryBundle\Repository\CategoryRequirementRepository;

/**
 * 分类搜索服务类
 * 
 * 提供高级搜索、筛选、排序等功能
 */
class CategorySearchService
{
    public function __construct(
        private readonly CategoryRepository $categoryRepository,
        private readonly CategoryRequirementRepository $requirementRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * 高级搜索分类
     */
    /**
     * @return array<string, mixed>
     */
    public function advancedSearch(array $criteria): array
    {
        $qb = $this->categoryRepository->createQueryBuilder('c');
        
        // 左连接培训要求表
        $qb->leftJoin('Tourze\TrainCategoryBundle\Entity\CategoryRequirement', 'cr', 'WITH', 'cr.category = c');
        
        $this->applyCriteria($qb, $criteria);
        
        // 默认排序
        $qb->orderBy('c.sortNumber', 'DESC')
           ->addOrderBy('c.id', 'DESC');

        return $qb->getQuery()->getResult();
    }

    /**
     * 按关键词搜索
     */
    /**
     * @return array<string, mixed>
     */
    public function searchByKeyword(string $keyword, array $options = []): array
    {
        $qb = $this->categoryRepository->createQueryBuilder('c');
        
        // 搜索标题
        $qb->where('c.title LIKE :keyword')
           ->setParameter('keyword', "%{$keyword}%");

        // 是否包含子分类搜索
        if ((bool) $options['includeChildren'] ?? false) {
            $qb->orWhere('EXISTS (SELECT 1 FROM Tourze\TrainCategoryBundle\Entity\Category child WHERE child.parent = c AND child.title LIKE :keyword)');
        }

        // 是否搜索培训要求备注
        if ((bool) $options['includeRequirements'] ?? false) {
            $qb->leftJoin('Tourze\TrainCategoryBundle\Entity\CategoryRequirement', 'cr', 'WITH', 'cr.category = c')
               ->orWhere('cr.remarks LIKE :keyword');
        }

        // 限制结果数量
        if ((bool) isset($options['limit'])) {
            $qb->setMaxResults($options['limit']);
        }

        $qb->orderBy('c.sortNumber', 'DESC')
           ->addOrderBy('c.id', 'DESC');

        return $qb->getQuery()->getResult();
    }

    /**
     * 按层级搜索
     */
    /**
     * @return array<string, mixed>
     */
    public function searchByLevel(int $level, array $filters = []): array
    {
        $qb = $this->categoryRepository->createQueryBuilder('c');
        
        // 构建层级查询
        $this->buildLevelQuery($qb, $level);
        
        // 应用额外筛选条件
        $this->applyFilters($qb, $filters);
        
        $qb->orderBy('c.sortNumber', 'DESC')
           ->addOrderBy('c.id', 'DESC');

        return $qb->getQuery()->getResult();
    }

    /**
     * 按培训要求搜索
     */
    /**
     * @return array<string, mixed>
     */
    public function searchByRequirements(array $requirements): array
    {
        $qb = $this->categoryRepository->createQueryBuilder('c');
        $qb->innerJoin('Tourze\TrainCategoryBundle\Entity\CategoryRequirement', 'cr', 'WITH', 'cr.category = c');

        // 学时要求
        if ((bool) isset($requirements['minInitialHours'])) {
            $qb->andWhere('cr.initialTrainingHours >= :minInitialHours')
               ->setParameter('minInitialHours', $requirements['minInitialHours']);
        }

        if ((bool) isset($requirements['maxInitialHours'])) {
            $qb->andWhere('cr.initialTrainingHours <= :maxInitialHours')
               ->setParameter('maxInitialHours', $requirements['maxInitialHours']);
        }

        // 证书有效期
        if ((bool) isset($requirements['validityPeriod'])) {
            $qb->andWhere('cr.certificateValidityPeriod = :validityPeriod')
               ->setParameter('validityPeriod', $requirements['validityPeriod']);
        }

        // 实操考试要求
        if ((bool) isset($requirements['requiresPracticalExam'])) {
            $qb->andWhere('cr.requiresPracticalExam = :requiresPracticalExam')
               ->setParameter('requiresPracticalExam', $requirements['requiresPracticalExam']);
        }

        // 现场培训要求
        if ((bool) isset($requirements['requiresOnSiteTraining'])) {
            $qb->andWhere('cr.requiresOnSiteTraining = :requiresOnSiteTraining')
               ->setParameter('requiresOnSiteTraining', $requirements['requiresOnSiteTraining']);
        }

        // 年龄范围
        if ((bool) isset($requirements['ageRange'])) {
            $ageRange = $requirements['ageRange'];
            if ((bool) isset($ageRange['min'])) {
                $qb->andWhere('cr.minimumAge <= :userAge')
                   ->setParameter('userAge', $ageRange['min']);
            }
            if ((bool) isset($ageRange['max'])) {
                $qb->andWhere('cr.maximumAge >= :userAge')
                   ->setParameter('userAge', $ageRange['max']);
            }
        }

        $qb->orderBy('c.sortNumber', 'DESC')
           ->addOrderBy('c.id', 'DESC');

        return $qb->getQuery()->getResult();
    }

    /**
     * 智能推荐分类
     */
    /**
     * @return array<string, mixed>
     */
    public function recommendCategories(array $userProfile): array
    {
        $recommendations = [];

        // 基于用户年龄推荐
        if ((bool) isset($userProfile['age'])) {
            $ageBasedCategories = $this->searchByRequirements([
                'ageRange' => ['min' => $userProfile['age'], 'max' => $userProfile['age']]
            ]);
            $recommendations['age_based'] = $ageBasedCategories;
        }

        // 基于用户行业推荐
        if ((bool) isset($userProfile['industry'])) {
            $industryCategories = $this->searchByKeyword($userProfile['industry'], ['includeChildren' => true]);
            $recommendations['industry_based'] = $industryCategories;
        }

        // 基于用户经验推荐
        if ((bool) isset($userProfile['experience'])) {
            $experienceLevel = $userProfile['experience'];
            if ($experienceLevel < 2) {
                // 新手推荐基础培训
                $basicCategories = $this->searchByRequirements([
                    'maxInitialHours' => 48,
                    'requiresPracticalExam' => false
                ]);
                $recommendations['beginner'] = $basicCategories;
            } else {
                // 有经验者推荐高级培训
                $advancedCategories = $this->searchByRequirements([
                    'minInitialHours' => 72,
                    'requiresPracticalExam' => true
                ]);
                $recommendations['advanced'] = $advancedCategories;
            }
        }

        return $recommendations;
    }

    /**
     * 获取热门分类
     */
    /**
     * @return array<string, mixed>
     */
    public function getPopularCategories(int $limit = 10): array
    {
        $qb = $this->categoryRepository->createQueryBuilder('c');
        
        // 这里可以基于实际的使用统计来排序
        // 目前基于子分类数量来判断热门程度
        $qb->leftJoin('c.children', 'children')
           ->groupBy('c.id')
           ->orderBy('COUNT(DISTINCT children.id)', 'DESC')
           ->addOrderBy('c.sortNumber', 'DESC')
           ->setMaxResults($limit);

        return $qb->getQuery()->getResult();
    }

    /**
     * 获取相关分类
     */
    /**
     * @return array<string, mixed>
     */
    public function getRelatedCategories(Category $category, int $limit = 5): array
    {
        $related = [];

        // 同级分类
        if ($category->getParent()) {
            $siblings = $this->categoryRepository->findBy(
                ['parent' => $category->getParent()],
                ['sortNumber' => 'DESC'],
                $limit
            );
            $related['siblings'] = array_filter($siblings, fn($c) => $c->getId() !== $category->getId());
        }

        // 子分类
        $children = $this->categoryRepository->findBy(
            ['parent' => $category],
            ['sortNumber' => 'DESC'],
            $limit
        );
        $related['children'] = $children;

        // 相似要求的分类
        $requirement = $this->requirementRepository->findByCategory($category);
        if ((bool) $requirement) {
            $similarCategories = $this->searchByRequirements([
                'validityPeriod' => $requirement->getCertificateValidityPeriod(),
                'requiresPracticalExam' => $requirement->isRequiresPracticalExam(),
            ]);
            $related['similar'] = array_filter(
                array_slice($similarCategories, 0, $limit),
                fn($c) => $c->getId() !== $category->getId()
            );
        }

        return $related;
    }

    /**
     * 分面搜索（Faceted Search）
     */
    /**
     * @return array<string, mixed>
     */
    public function facetedSearch(array $facets = []): array
    {
        $results = [
            'categories' => [],
            'facets' => [
                'levels' => [],
                'requirements' => [],
                'validity_periods' => [],
                'age_ranges' => [],
            ]
        ];

        // 执行搜索
        $categories = $this->advancedSearch($facets);
        $results['categories'] = $categories;

        // 计算分面统计
        $results['facets']['levels'] = $this->calculateLevelFacets($categories);
        $results['facets']['requirements'] = $this->calculateRequirementFacets();
        $results['facets']['validity_periods'] = $this->calculateValidityPeriodFacets();
        $results['facets']['age_ranges'] = $this->calculateAgeRangeFacets();

        return $results;
    }

    /**
     * 应用搜索条件
     */
    private function applyCriteria(QueryBuilder $qb, array $criteria): void
    {
        // 标题搜索
        if (!empty($criteria['title'])) {
            $qb->andWhere('c.title LIKE :title')
               ->setParameter('title', "%{$criteria['title']}%");
        }

        // 父分类筛选
        if ((bool) isset($criteria['parent'])) {
            if ($criteria['parent'] === null) {
                $qb->andWhere('c.parent IS NULL');
            } else {
                $qb->andWhere('c.parent = :parent')
                   ->setParameter('parent', $criteria['parent']);
            }
        }

        // 层级筛选
        if ((bool) isset($criteria['level'])) {
            $this->buildLevelQuery($qb, $criteria['level']);
        }

        // 排序值范围
        if ((bool) isset($criteria['sortRange'])) {
            $range = $criteria['sortRange'];
            if ((bool) isset($range['min'])) {
                $qb->andWhere('c.sortNumber >= :minSort')
                   ->setParameter('minSort', $range['min']);
            }
            if ((bool) isset($range['max'])) {
                $qb->andWhere('c.sortNumber <= :maxSort')
                   ->setParameter('maxSort', $range['max']);
            }
        }

        // 创建时间范围
        if ((bool) isset($criteria['dateRange'])) {
            $range = $criteria['dateRange'];
            if ((bool) isset($range['start'])) {
                $qb->andWhere('c.createTime >= :startDate')
                   ->setParameter('startDate', $range['start']);
            }
            if ((bool) isset($range['end'])) {
                $qb->andWhere('c.createTime <= :endDate')
                   ->setParameter('endDate', $range['end']);
            }
        }

        // 培训要求筛选
        if (!empty($criteria['requirements'])) {
            $this->applyRequirementCriteria($qb, $criteria['requirements']);
        }
    }

    /**
     * 应用培训要求筛选条件
     */
    private function applyRequirementCriteria(QueryBuilder $qb, array $requirements): void
    {
        if ((bool) isset($requirements['hasRequirements'])) {
            if ((bool) $requirements['hasRequirements']) {
                $qb->andWhere('cr.id IS NOT NULL');
            } else {
                $qb->andWhere('cr.id IS NULL');
            }
        }

        if ((bool) isset($requirements['minHours'])) {
            $qb->andWhere('cr.initialTrainingHours >= :minHours')
               ->setParameter('minHours', $requirements['minHours']);
        }

        if ((bool) isset($requirements['maxHours'])) {
            $qb->andWhere('cr.initialTrainingHours <= :maxHours')
               ->setParameter('maxHours', $requirements['maxHours']);
        }

        if ((bool) isset($requirements['practicalExam'])) {
            $qb->andWhere('cr.requiresPracticalExam = :practicalExam')
               ->setParameter('practicalExam', $requirements['practicalExam']);
        }

        if ((bool) isset($requirements['onSiteTraining'])) {
            $qb->andWhere('cr.requiresOnSiteTraining = :onSiteTraining')
               ->setParameter('onSiteTraining', $requirements['onSiteTraining']);
        }
    }

    /**
     * 构建层级查询
     */
    private function buildLevelQuery(QueryBuilder $qb, int $level): void
    {
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
            $qb->andWhere('c.parent IS NULL');
        } else {
            $prevAlias = 'p' . ($level - 2);
            $currentAlias = 'p' . ($level - 1);
            $qb->andWhere($prevAlias . ' IS NOT NULL')
               ->andWhere($currentAlias . ' IS NULL');
        }
    }

    /**
     * 应用筛选条件
     */
    private function applyFilters(QueryBuilder $qb, array $filters): void
    {
        foreach ($filters as $field => $value) {
            switch ($field) {
                case 'hasChildren':
                    if ((bool) $value) {
                        $qb->andWhere('EXISTS (SELECT 1 FROM Tourze\TrainCategoryBundle\Entity\Category child WHERE child.parent = c)');
                    } else {
                        $qb->andWhere('NOT EXISTS (SELECT 1 FROM Tourze\TrainCategoryBundle\Entity\Category child WHERE child.parent = c)');
                    }
                    break;
            }
        }
    }

    /**
     * 计算层级分面统计
     */
    private function calculateLevelFacets(array $categories): array
    {
        $levelCounts = [];
        foreach ($categories as $category) {
            $level = $this->calculateCategoryLevel($category);
            $levelCounts[$level] = ($levelCounts[$level] ?? 0) + 1;
        }
        return $levelCounts;
    }

    /**
     * 计算分类层级
     */
    private function calculateCategoryLevel(Category $category): int
    {
        $level = 1;
        $current = $category;
        while ($current->getParent() !== null) {
            $level++;
            $current = $current->getParent();
        }
        return $level;
    }

    /**
     * 计算培训要求分面统计
     */
    private function calculateRequirementFacets(): array
    {
        return $this->requirementRepository->getRequirementStatistics();
    }

    /**
     * 计算证书有效期分面统计
     */
    private function calculateValidityPeriodFacets(): array
    {
        $periods = $this->requirementRepository->getDistinctValidityPeriods();
        $facets = [];
        foreach ($periods as $period) {
            $count = count($this->requirementRepository->findByValidityPeriod($period));
            $facets[$period] = $count;
        }
        return $facets;
    }

    /**
     * 计算年龄范围分面统计
     */
    private function calculateAgeRangeFacets(): array
    {
        // 定义年龄段
        $ageRanges = [
            '16-25' => [16, 25],
            '26-35' => [26, 35],
            '36-45' => [36, 45],
            '46-55' => [46, 55],
            '56-65' => [56, 65],
            '65+' => [65, 100],
        ];

        $facets = [];
        foreach ($ageRanges as $label => $range) {
            $count = count($this->searchByRequirements([
                'ageRange' => ['min' => $range[0], 'max' => $range[1]]
            ]));
            $facets[$label] = $count;
        }

        return $facets;
    }
} 