<?php

namespace Tourze\TrainCategoryBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Tourze\TrainCategoryBundle\Entity\Category;
use Tourze\TrainCategoryBundle\Repository\CategoryRepository;
use Tourze\TrainCategoryBundle\Repository\CategoryRequirementRepository;

/**
 * 分类统计分析服务类
 * 
 * 提供分类数据的统计分析、报表生成等功能
 */
class CategoryStatisticsService
{
    public function __construct(
        private readonly CategoryRepository $categoryRepository,
        private readonly CategoryRequirementRepository $requirementRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * 获取分类概览统计
     */
    public function getOverviewStatistics(): array
    {
        $stats = [
            'total_categories' => 0,
            'root_categories' => 0,
            'leaf_categories' => 0,
            'categories_with_requirements' => 0,
            'avg_children_per_category' => 0,
            'max_depth' => 0,
            'creation_trend' => [],
        ];

        // 基础统计
        $stats['total_categories'] = $this->categoryRepository->count([]);
        $stats['root_categories'] = $this->categoryRepository->count(['parent' => null]);
        
        // 叶子分类统计
        $qb = $this->categoryRepository->createQueryBuilder('c');
        $qb->leftJoin('c.children', 'children')
           ->where('children.id IS NULL');
        $stats['leaf_categories'] = count($qb->getQuery()->getResult());

        // 有培训要求的分类统计
        $stats['categories_with_requirements'] = $this->requirementRepository->count([]);

        // 平均子分类数量
        $qb = $this->categoryRepository->createQueryBuilder('c');
        $qb->select('AVG(SIZE(c.children)) as avg_children');
        $result = $qb->getQuery()->getSingleScalarResult();
        $avgChildrenResult = is_numeric($result) ? (float) $result : 0.0;
        $stats['avg_children_per_category'] = round($avgChildrenResult, 2);

        // 最大深度
        $stats['max_depth'] = $this->calculateMaxDepth();

        // 创建趋势
        $stats['creation_trend'] = $this->getCreationTrend();

        return $stats;
    }

    /**
     * 获取分类层级分布统计
     * @return array<string, mixed>
     */
    public function getLevelDistribution(): array
    {
        $distribution = [];
        $allCategories = $this->categoryRepository->findAll();

        foreach ($allCategories as $category) {
            $level = $this->calculateCategoryLevel($category);
            $distribution[$level] = ($distribution[$level] ?? 0) + 1;
        }

        ksort($distribution);
        return $distribution;
    }

    /**
     * 获取培训要求统计
     * @return array<string, mixed>
     */
    public function getRequirementStatistics(): array
    {
        $stats = $this->requirementRepository->getRequirementStatistics();
        
        // 添加更多详细统计
        $additionalStats = [
            'hours_distribution' => $this->getHoursDistribution(),
            'validity_period_distribution' => $this->getValidityPeriodDistribution(),
            'age_requirement_distribution' => $this->getAgeRequirementDistribution(),
            'exam_requirement_stats' => $this->getExamRequirementStats(),
        ];

        return array_merge($stats, $additionalStats);
    }

    /**
     * 获取热门分类排行
     * @return array<int, array<string, mixed>>
     */
    public function getPopularCategoriesRanking(int $limit = 20): array
    {
        $qb = $this->categoryRepository->createQueryBuilder('c');
        
        $qb->select([
            'c.id',
            'c.title',
            'COUNT(DISTINCT children.id) as children_count',
            'COUNT(DISTINCT children.id) as popularity_score'
        ])
        ->leftJoin('c.children', 'children')
        ->groupBy('c.id')
        ->orderBy('popularity_score', 'DESC')
        ->setMaxResults($limit);

        /** @var array<int, array<string, mixed>> */
        return $qb->getQuery()->getResult();
    }

    /**
     * 获取分类使用情况分析
     * @return array<string, mixed>
     */
    public function getCategoryUsageAnalysis(): array
    {
        $analysis = [
            'unused_categories' => [],
            'heavily_used_categories' => [],
            'categories_without_requirements' => [],
            'categories_without_children' => [],
        ];

        // 未使用的分类（没有子分类）
        $qb = $this->categoryRepository->createQueryBuilder('c');
        $qb->leftJoin('c.children', 'children')
           ->where('children.id IS NULL');
        $analysis['unused_categories'] = $qb->getQuery()->getResult();

        // 使用频繁的分类（子分类数量 > 5）
        $qb = $this->categoryRepository->createQueryBuilder('c');
        $qb->select('c')
           ->leftJoin('c.children', 'children')
           ->groupBy('c.id')
           ->having('COUNT(DISTINCT children.id) > 5');
        $analysis['heavily_used_categories'] = $qb->getQuery()->getResult();

        // 没有培训要求的分类
        $qb = $this->categoryRepository->createQueryBuilder('c');
        $qb->leftJoin('Tourze\TrainCategoryBundle\Entity\CategoryRequirement', 'cr', 'WITH', 'cr.category = c')
           ->where('cr.id IS NULL');
        $analysis['categories_without_requirements'] = $qb->getQuery()->getResult();

        // 没有子分类的分类
        $qb = $this->categoryRepository->createQueryBuilder('c');
        $qb->leftJoin('c.children', 'children')
           ->where('children.id IS NULL');
        $analysis['categories_without_children'] = $qb->getQuery()->getResult();

        return $analysis;
    }

    /**
     * 获取时间趋势分析
     */
    public function getTimeTrendAnalysis(string $period = 'month'): array
    {
        $trends = [
            'category_creation' => $this->getCategoryCreationTrend($period),
            'requirement_creation' => $this->getRequirementCreationTrend($period),
        ];

        return $trends;
    }

    /**
     * 生成分类健康度报告
     */
    public function generateHealthReport(): array
    {
        $report = [
            'overall_health' => 'good',
            'issues' => [],
            'recommendations' => [],
            'scores' => [
                'structure_score' => 0,
                'completeness_score' => 0,
                'usage_score' => 0,
                'overall_score' => 0,
            ],
        ];

        // 结构健康度评分
        $structureIssues = $this->analyzeStructureHealth();
        $report['scores']['structure_score'] = $this->calculateStructureScore($structureIssues);

        // 完整性评分
        $completenessIssues = $this->analyzeCompletenessHealth();
        $report['scores']['completeness_score'] = $this->calculateCompletenessScore($completenessIssues);

        // 使用情况评分
        $usageIssues = $this->analyzeUsageHealth();
        $report['scores']['usage_score'] = $this->calculateUsageScore($usageIssues);

        // 综合评分
        $report['scores']['overall_score'] = round(
            ($report['scores']['structure_score'] + 
             $report['scores']['completeness_score'] + 
             $report['scores']['usage_score']) / 3, 
            1
        );

        // 收集所有问题
        $report['issues'] = array_merge($structureIssues, $completenessIssues, $usageIssues);

        // 生成建议
        $report['recommendations'] = $this->generateRecommendations($report['issues']);

        // 确定整体健康度
        if ($report['scores']['overall_score'] >= 8.0) {
            $report['overall_health'] = 'excellent';
        } elseif ($report['scores']['overall_score'] >= 6.0) {
            $report['overall_health'] = 'good';
        } elseif ($report['scores']['overall_score'] >= 4.0) {
            $report['overall_health'] = 'fair';
        } else {
            $report['overall_health'] = 'poor';
        }

        return $report;
    }

    /**
     * 导出统计报表
     */
    public function exportStatisticsReport(string $format = 'array'): array
    {
        $report = [
            'generated_at' => new \DateTime(),
            'overview' => $this->getOverviewStatistics(),
            'level_distribution' => $this->getLevelDistribution(),
            'requirement_statistics' => $this->getRequirementStatistics(),
            'popular_categories' => $this->getPopularCategoriesRanking(),
            'usage_analysis' => $this->getCategoryUsageAnalysis(),
            'time_trends' => $this->getTimeTrendAnalysis(),
            'health_report' => $this->generateHealthReport(),
        ];

        // 根据格式处理数据
        switch ($format) {
            case 'json':
                return ['data' => json_encode($report, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)];
            case 'csv':
                return $this->convertToCSV($report);
            default:
                return $report;
        }
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
     * 计算最大深度
     */
    private function calculateMaxDepth(): int
    {
        $maxDepth = 0;
        $allCategories = $this->categoryRepository->findAll();

        foreach ($allCategories as $category) {
            $depth = $this->calculateCategoryLevel($category);
            $maxDepth = max($maxDepth, $depth);
        }

        return $maxDepth;
    }

    /**
     * 获取创建趋势
     */
    private function getCreationTrend(int $months = 12): array
    {
        $trend = [];
        $startDate = new \DateTime("-{$months} months");

        for ($i = 0; $i < $months; $i++) {
            $monthStart = clone $startDate;
            $monthStart->modify("+{$i} months");
            $monthEnd = clone $monthStart;
            $monthEnd->modify('+1 month');

            $count = $this->categoryRepository->createQueryBuilder('c')
                ->select('COUNT(c.id)')
                ->where('c.createTime >= :start')
                ->andWhere('c.createTime < :end')
                ->setParameter('start', $monthStart)
                ->setParameter('end', $monthEnd)
                ->getQuery()
                ->getSingleScalarResult();

            $trend[$monthStart->format('Y-m')] = (int) $count;
        }

        return $trend;
    }

    /**
     * 获取学时分布统计
     */
    private function getHoursDistribution(): array
    {
        $ranges = [
            '0-24' => [0, 24],
            '25-48' => [25, 48],
            '49-72' => [49, 72],
            '73-96' => [73, 96],
            '97+' => [97, 9999],
        ];

        $distribution = [];
        foreach ($ranges as $label => $range) {
            $count = $this->requirementRepository->createQueryBuilder('cr')
                ->select('COUNT(cr.id)')
                ->where('cr.initialTrainingHours >= :min')
                ->andWhere('cr.initialTrainingHours <= :max')
                ->setParameter('min', $range[0])
                ->setParameter('max', $range[1])
                ->getQuery()
                ->getSingleScalarResult();

            $distribution[$label] = (int) $count;
        }

        return $distribution;
    }

    /**
     * 获取证书有效期分布
     */
    private function getValidityPeriodDistribution(): array
    {
        $qb = $this->requirementRepository->createQueryBuilder('cr');
        $qb->select('cr.certificateValidityPeriod, COUNT(cr.id) as count')
           ->groupBy('cr.certificateValidityPeriod')
           ->orderBy('cr.certificateValidityPeriod', 'ASC');

        $results = $qb->getQuery()->getResult();
        $distribution = [];

        foreach ($results as $result) {
            $distribution[$result['certificateValidityPeriod'] . '个月'] = (int) $result['count'];
        }

        return $distribution;
    }

    /**
     * 获取年龄要求分布
     */
    private function getAgeRequirementDistribution(): array
    {
        $qb = $this->requirementRepository->createQueryBuilder('cr');
        $qb->select('cr.minimumAge, cr.maximumAge, COUNT(cr.id) as count')
           ->groupBy('cr.minimumAge, cr.maximumAge')
           ->orderBy('cr.minimumAge', 'ASC');

        $results = $qb->getQuery()->getResult();
        $distribution = [];

        foreach ($results as $result) {
            $label = $result['minimumAge'] . '-' . $result['maximumAge'] . '岁';
            $distribution[$label] = (int) $result['count'];
        }

        return $distribution;
    }

    /**
     * 获取考试要求统计
     */
    private function getExamRequirementStats(): array
    {
        $practicalExamCount = $this->requirementRepository->count(['requiresPracticalExam' => true]);
        $onSiteTrainingCount = $this->requirementRepository->count(['requiresOnSiteTraining' => true]);
        $totalCount = $this->requirementRepository->count([]);

        return [
            'practical_exam_required' => $practicalExamCount,
            'onsite_training_required' => $onSiteTrainingCount,
            'practical_exam_percentage' => $totalCount > 0 ? round(($practicalExamCount / $totalCount) * 100, 1) : 0,
            'onsite_training_percentage' => $totalCount > 0 ? round(($onSiteTrainingCount / $totalCount) * 100, 1) : 0,
        ];
    }

    /**
     * 获取分类创建趋势
     */
    private function getCategoryCreationTrend(string $period): array
    {
        $format = match ($period) {
            'day' => 'Y-m-d',
            'week' => 'Y-W',
            'month' => 'Y-m',
            'year' => 'Y',
            default => 'Y-m',
        };

        $qb = $this->categoryRepository->createQueryBuilder('c');
        $qb->select("DATE_FORMAT(c.createTime, '{$format}') as period, COUNT(c.id) as count")
           ->groupBy('period')
           ->orderBy('period', 'ASC');

        return $qb->getQuery()->getResult();
    }

    /**
     * 获取培训要求创建趋势
     */
    private function getRequirementCreationTrend(string $period): array
    {
        $format = match ($period) {
            'day' => 'Y-m-d',
            'week' => 'Y-W',
            'month' => 'Y-m',
            'year' => 'Y',
            default => 'Y-m',
        };

        $qb = $this->requirementRepository->createQueryBuilder('cr');
        $qb->select("DATE_FORMAT(cr.createTime, '{$format}') as period, COUNT(cr.id) as count")
           ->groupBy('period')
           ->orderBy('period', 'ASC');

        return $qb->getQuery()->getResult();
    }

    /**
     * 分析结构健康度
     */
    private function analyzeStructureHealth(): array
    {
        $issues = [];

        // 检查孤立分类（没有父分类也没有子分类）
        $qb = $this->categoryRepository->createQueryBuilder('c');
        $qb->leftJoin('c.children', 'children')
           ->where('c.parent IS NULL')
           ->andWhere('children.id IS NULL');
        $orphanCategories = $qb->getQuery()->getResult();

        if ((bool) count($orphanCategories) > 0) {
            $issues[] = [
                'type' => 'structure',
                'severity' => 'medium',
                'message' => '发现 ' . count($orphanCategories) . ' 个孤立分类（既无父分类也无子分类）',
                'count' => count($orphanCategories),
            ];
        }

        // 检查过深的层级
        $maxDepth = $this->calculateMaxDepth();
        if ($maxDepth > 5) {
            $issues[] = [
                'type' => 'structure',
                'severity' => 'high',
                'message' => "分类层级过深（{$maxDepth}级），建议不超过5级",
                'value' => $maxDepth,
            ];
        }

        return $issues;
    }

    /**
     * 分析完整性健康度
     */
    private function analyzeCompletenessHealth(): array
    {
        $issues = [];

        // 检查缺少培训要求的分类
        $totalCategories = $this->categoryRepository->count([]);
        $categoriesWithRequirements = $this->requirementRepository->count([]);
        $missingRequirements = $totalCategories - $categoriesWithRequirements;

        if ($missingRequirements > 0) {
            $percentage = round(($missingRequirements / $totalCategories) * 100, 1);
            $issues[] = [
                'type' => 'completeness',
                'severity' => $percentage > 50 ? 'high' : 'medium',
                'message' => "有 {$missingRequirements} 个分类（{$percentage}%）缺少培训要求配置",
                'count' => $missingRequirements,
                'percentage' => $percentage,
            ];
        }

        return $issues;
    }

    /**
     * 分析使用情况健康度
     */
    private function analyzeUsageHealth(): array
    {
        $issues = [];

        // 检查未使用的分类
        $usageAnalysis = $this->getCategoryUsageAnalysis();
        $unusedCount = count($usageAnalysis['unused_categories']);

        if ($unusedCount > 0) {
            $totalCategories = $this->categoryRepository->count([]);
            $percentage = round(($unusedCount / $totalCategories) * 100, 1);
            $issues[] = [
                'type' => 'usage',
                'severity' => $percentage > 30 ? 'high' : 'medium',
                'message' => "有 {$unusedCount} 个分类（{$percentage}%）未被使用",
                'count' => $unusedCount,
                'percentage' => $percentage,
            ];
        }

        return $issues;
    }

    /**
     * 计算结构评分
     */
    private function calculateStructureScore(array $issues): float
    {
        $score = 10.0;
        foreach ($issues as $issue) {
            if ($issue['type'] === 'structure') {
                $deduction = match ($issue['severity']) {
                    'high' => 3.0,
                    'medium' => 1.5,
                    'low' => 0.5,
                    default => 1.0,
                };
                $score -= $deduction;
            }
        }
        return max(0, $score);
    }

    /**
     * 计算完整性评分
     */
    private function calculateCompletenessScore(array $issues): float
    {
        $score = 10.0;
        foreach ($issues as $issue) {
            if ($issue['type'] === 'completeness') {
                $deduction = match ($issue['severity']) {
                    'high' => 4.0,
                    'medium' => 2.0,
                    'low' => 1.0,
                    default => 1.5,
                };
                $score -= $deduction;
            }
        }
        return max(0, $score);
    }

    /**
     * 计算使用情况评分
     */
    private function calculateUsageScore(array $issues): float
    {
        $score = 10.0;
        foreach ($issues as $issue) {
            if ($issue['type'] === 'usage') {
                $deduction = match ($issue['severity']) {
                    'high' => 3.5,
                    'medium' => 2.0,
                    'low' => 0.5,
                    default => 1.5,
                };
                $score -= $deduction;
            }
        }
        return max(0, $score);
    }

    /**
     * 生成建议
     */
    private function generateRecommendations(array $issues): array
    {
        $recommendations = [];

        foreach ($issues as $issue) {
            switch ($issue['type']) {
                case 'structure':
                    if ((bool) str_contains($issue['message'], '孤立分类')) {
                        $recommendations[] = '建议为孤立分类添加父分类或子分类，或考虑删除不必要的分类';
                    }
                    if ((bool) str_contains($issue['message'], '层级过深')) {
                        $recommendations[] = '建议重新设计分类结构，将深层级分类提升或合并';
                    }
                    break;
                case 'completeness':
                    if ((bool) str_contains($issue['message'], '缺少培训要求')) {
                        $recommendations[] = '建议为所有分类配置相应的培训要求，确保符合AQ8011-2023标准';
                    }
                    break;
                case 'usage':
                    if ((bool) str_contains($issue['message'], '未被使用')) {
                        $recommendations[] = '建议清理未使用的分类，或为其添加相关内容（子分类或题库）';
                    }
                    break;
            }
        }

        return array_unique($recommendations);
    }

    /**
     * 转换为CSV格式
     */
    private function convertToCSV(array $data): array
    {
        // 这里可以实现CSV转换逻辑
        // 简化实现，返回基础统计的CSV格式
        $csv = [];
        $csv[] = ['指标', '数值'];
        
        if ((bool) isset($data['overview'])) {
            foreach ($data['overview'] as $key => $value) {
                if (!is_array($value)) {
                    $csv[] = [$key, $value];
                }
            }
        }

        return ['csv_data' => $csv];
    }
} 