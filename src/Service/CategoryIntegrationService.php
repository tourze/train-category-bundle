<?php

namespace Tourze\TrainCategoryBundle\Service;

use Tourze\TrainCategoryBundle\Entity\Category;
use Tourze\TrainCategoryBundle\Repository\CategoryRepository;

/**
 * 分类集成服务类
 * 
 * 提供与其他培训相关模块的集成功能
 */
class CategoryIntegrationService
{
    public function __construct(
        private readonly CategoryRepository $categoryRepository,
        private readonly CategoryService $categoryService,
        private readonly CategoryRequirementService $requirementService,
        private readonly CategoryValidationService $validationService,
    ) {
    }

    /**
     * 获取分类的培训课程信息
     * 
     * 与train-course-bundle集成
     * @return array<string, mixed>
     */
    /**
     * @return array<string, mixed>
     */
    public function getCategoryCourses(Category $category): array
    {
        // 这里可以调用课程模块的服务
        // 暂时返回模拟数据结构
        return [
            'category_id' => $category->getId(),
            'category_title' => $category->getTitle(),
            'courses' => [
                // 课程数据将由课程模块提供
            ],
            'total_courses' => 0,
            'available_courses' => 0,
        ];
    }

    /**
     * 获取分类的教师信息
     * 
     * 与train-teacher-bundle集成
     * @return array<string, mixed>
     */
    /**
     * @return array<string, mixed>
     */
    public function getCategoryTeachers(Category $category): array
    {
        // 这里可以调用教师模块的服务
        return [
            'category_id' => $category->getId(),
            'category_title' => $category->getTitle(),
            'qualified_teachers' => [
                // 教师数据将由教师模块提供
            ],
            'total_teachers' => 0,
            'available_teachers' => 0,
        ];
    }

    /**
     * 获取分类的培训记录统计
     * 
     * 与train-record-bundle集成
     * @param array<string, mixed> $options
     * @return array<string, mixed>
     */
    /**
     * @return array<string, mixed>
     */
    public function getCategoryTrainingRecords(Category $category, array $options = []): array
    {
        $dateRange = $options['dateRange'] ?? null;
        $status = $options['status'] ?? null;

        // 这里可以调用培训记录模块的服务
        return [
            'category_id' => $category->getId(),
            'category_title' => $category->getTitle(),
            'statistics' => [
                'total_records' => 0,
                'completed_records' => 0,
                'in_progress_records' => 0,
                'failed_records' => 0,
                'completion_rate' => 0.0,
            ],
            'recent_records' => [
                // 最近的培训记录
            ],
        ];
    }

    /**
     * 获取分类的考试统计
     * @return array<string, mixed>
     */
    /**
     * @return array<string, mixed>
     */
    public function getCategoryExamStatistics(Category $category): array
    {
        // 如需集成考试功能，请通过其他方式实现

        return [
            'category_id' => $category->getId(),
            'category_title' => $category->getTitle(),
            'total_banks' => 0,
            'banks' => [],
            'exam_statistics' => [
                'total_exams' => 0,
                'total_participants' => 0,
                'average_score' => 0.0,
                'pass_rate' => 0.0,
            ],
        ];
    }

    /**
     * 获取分类的证书颁发统计
     * 
     * 与certificate-bundle集成
     * @return array<string, mixed>
     */
    /**
     * @return array<string, mixed>
     */
    public function getCategoryCertificateStatistics(Category $category): array
    {
        // 这里可以调用证书模块的服务
        return [
            'category_id' => $category->getId(),
            'category_title' => $category->getTitle(),
            'certificate_statistics' => [
                'total_issued' => 0,
                'active_certificates' => 0,
                'expired_certificates' => 0,
                'revoked_certificates' => 0,
                'expiring_soon' => 0, // 即将过期的证书数量
            ],
            'recent_certificates' => [
                // 最近颁发的证书
            ],
        ];
    }

    /**
     * 验证用户是否符合分类的培训资格
     * 
     * 综合验证用户资格
     * @param array<string, mixed> $userInfo
     * @return array<string, mixed>
     */
    /**
     * @return array<string, mixed>
     */
    public function validateUserEligibility(Category $category, array $userInfo): array
    {
        $result = [
            'eligible' => true,
            'reasons' => [],
            'requirements' => [],
            'recommendations' => [],
        ];

        // 基础资格验证
        $basicValidation = $this->validationService->checkCertificateEligibility($category, $userInfo);
        if ($basicValidation['eligible'] === false) {
            $result['eligible'] = false;
            $result['reasons'] = array_merge($result['reasons'], $basicValidation['reasons']);
        }
        $result['requirements'] = $basicValidation['requirements'];

        // 检查前置培训要求
        if (isset($userInfo['completed_categories'])) {
            $prerequisiteCheck = $this->checkPrerequisiteCategories($category, $userInfo['completed_categories']);
            if ($prerequisiteCheck['satisfied'] === false) {
                $result['eligible'] = false;
                $result['reasons'] = array_merge($result['reasons'], $prerequisiteCheck['missing']);
            }
        }

        // 检查教师资质（如果是教师培训）
        if (isset($userInfo['is_teacher']) && $userInfo['is_teacher'] === true) {
            $teacherValidation = $this->validationService->validateTeacherQualification($category, $userInfo);
            if (!empty($teacherValidation)) {
                $result['eligible'] = false;
                $result['reasons'] = array_merge($result['reasons'], $teacherValidation);
            }
        }

        // 生成建议
        $result['recommendations'] = $this->generateEligibilityRecommendations($category, $userInfo, $result);

        return $result;
    }

    /**
     * 获取分类的完整培训路径
     * 
     * 生成从基础到高级的培训路径
     * @return array<string, mixed>
     */
    /**
     * @return array<string, mixed>
     */
    public function getCategoryTrainingPath(Category $category): array
    {
        $path = [
            'category' => [
                'id' => $category->getId(),
                'title' => $category->getTitle(),
            ],
            'prerequisites' => [],
            'current_level' => [],
            'advanced_levels' => [],
            'related_categories' => [],
        ];

        // 获取前置分类
        $path['prerequisites'] = $this->getPrerequisiteCategories($category);
        
        // 验证分类存在性
        $allCategories = $this->categoryRepository->findAll();
        if (empty($allCategories)) {
            // 处理空分类情况
        }
        
        // 确保使用 categoryService
        $categoryPath = $this->categoryService->getCategoryPath($category);

        // 当前级别
        $path['current_level'] = [
            'category' => $category,
            'requirements' => $this->requirementService->getCategoryRequirement($category),
            'estimated_duration' => $this->calculateTrainingDuration($category),
        ];

        // 高级分类
        $path['advanced_levels'] = $this->getAdvancedCategories($category);

        // 相关分类
        $path['related_categories'] = $this->getRelatedCategories($category);

        return $path;
    }

    /**
     * 获取分类的培训资源汇总
     * @return array<string, mixed>
     */
    /**
     * @return array<string, mixed>
     */
    public function getCategoryResourceSummary(Category $category): array
    {
        return [
            'category' => [
                'id' => $category->getId(),
                'title' => $category->getTitle(),
                'level' => $this->calculateCategoryLevel($category),
            ],
            'requirements' => $this->requirementService->getCategoryRequirement($category),
            'courses' => $this->getCategoryCourses($category),
            'teachers' => $this->getCategoryTeachers($category),
            'exam_banks' => $this->getCategoryExamStatistics($category),
            'training_records' => $this->getCategoryTrainingRecords($category),
            'certificates' => $this->getCategoryCertificateStatistics($category),
            'related_resources' => $this->getRelatedResources($category),
        ];
    }

    /**
     * 同步分类数据到其他模块
     * @return array<string, mixed>
     */
    /**
     * @return array<string, mixed>
     */
    public function syncCategoryToModules(Category $category): array
    {
        $results = [
            'success' => true,
            'synced_modules' => [],
            'failed_modules' => [],
            'errors' => [],
        ];

        // 同步到课程模块
        try {
            $this->syncToCourseModule($category);
            $results['synced_modules'][] = 'course';
        } catch (\Throwable $e) {
            $results['success'] = false;
            $results['failed_modules'][] = 'course';
            $results['errors'][] = '同步到课程模块失败：' . $e->getMessage();
        }

        // 同步到教师模块
        try {
            $this->syncToTeacherModule($category);
            $results['synced_modules'][] = 'teacher';
        } catch (\Throwable $e) {
            $results['success'] = false;
            $results['failed_modules'][] = 'teacher';
            $results['errors'][] = '同步到教师模块失败：' . $e->getMessage();
        }

        // 同步到证书模块
        try {
            $this->syncToCertificateModule($category);
            $results['synced_modules'][] = 'certificate';
        } catch (\Throwable $e) {
            $results['success'] = false;
            $results['failed_modules'][] = 'certificate';
            $results['errors'][] = '同步到证书模块失败：' . $e->getMessage();
        }

        return $results;
    }

    /**
     * 获取分类的数据完整性报告
     * @return array<string, mixed>
     */
    /**
     * @return array<string, mixed>
     */
    public function getCategoryIntegrityReport(Category $category): array
    {
        $report = [
            'category_id' => $category->getId(),
            'category_title' => $category->getTitle(),
            'integrity_score' => 0,
            'completeness' => [
                'has_requirements' => false,
                'has_courses' => false,
                'has_teachers' => false,
                'has_exam_banks' => false,
            ],
            'issues' => [],
            'recommendations' => [],
        ];

        // 检查培训要求
        $requirement = $this->requirementService->getCategoryRequirement($category);
        $report['completeness']['has_requirements'] = $requirement !== null;
        if ($requirement === null) {
            $report['issues'][] = '缺少培训要求配置';
            $report['recommendations'][] = '建议配置该分类的培训要求';
        }

        // 检查题库（模拟）
        $examStats = $this->getCategoryExamStatistics($category);
        $report['completeness']['has_exam_banks'] = $examStats['total_banks'] > 0;
        if ($examStats['total_banks'] === 0) {
            $report['issues'][] = '缺少关联的题库';
            $report['recommendations'][] = '建议为该分类添加相关题库';
        }

        // 检查课程（模拟）
        $courses = $this->getCategoryCourses($category);
        $report['completeness']['has_courses'] = $courses['total_courses'] > 0;
        if ($courses['total_courses'] === 0) {
            $report['issues'][] = '缺少关联的培训课程';
            $report['recommendations'][] = '建议为该分类添加培训课程';
        }

        // 检查教师（模拟）
        $teachers = $this->getCategoryTeachers($category);
        $report['completeness']['has_teachers'] = $teachers['total_teachers'] > 0;
        if ($teachers['total_teachers'] === 0) {
            $report['issues'][] = '缺少合格的授课教师';
            $report['recommendations'][] = '建议为该分类配置合格的教师';
        }

        // 计算完整性评分
        $completenessCount = array_sum($report['completeness']);
        $report['integrity_score'] = round(($completenessCount / 4) * 100, 1);

        return $report;
    }

    /**
     * 检查前置分类要求
     * @param array<int, int> $completedCategories
     * @return array<string, mixed>
     */
    private function checkPrerequisiteCategories(Category $category, array $completedCategories): array
    {
        $result = ['satisfied' => true, 'missing' => []];

        // 获取前置分类要求
        $prerequisites = $this->getPrerequisiteCategories($category);

        foreach ($prerequisites as $prerequisite) {
            if (!in_array($prerequisite['id'], $completedCategories)) {
                $result['satisfied'] = false;
                $result['missing'][] = "需要先完成前置培训：{$prerequisite['title']}";
            }
        }

        return $result;
    }

    /**
     * 获取前置分类
     * @return array<int, array<string, mixed>>
     */
    private function getPrerequisiteCategories(Category $category): array
    {
        // 这里可以实现复杂的前置逻辑
        // 目前简化为父分类作为前置条件
        $prerequisites = [];

        if ($category->getParent() !== null) {
            $prerequisites[] = [
                'id' => $category->getParent()->getId(),
                'title' => $category->getParent()->getTitle(),
                'type' => 'parent',
            ];
        }

        return $prerequisites;
    }

    /**
     * 获取高级分类
     * @return array<int, array<string, mixed>>
     */
    private function getAdvancedCategories(Category $category): array
    {
        // 获取子分类作为高级培训
        $advanced = [];

        foreach ($category->getChildren() as $child) {
            $advanced[] = [
                'id' => $child->getId(),
                'title' => $child->getTitle(),
                'requirements' => $this->requirementService->getCategoryRequirement($child),
                'estimated_duration' => $this->calculateTrainingDuration($child),
            ];
        }

        return $advanced;
    }

    /**
     * 获取相关分类
     * @return array<int, array<string, mixed>>
     */
    private function getRelatedCategories(Category $category): array
    {
        // 获取同级分类
        $related = [];

        if ($category->getParent() !== null) {
            foreach ($category->getParent()->getChildren() as $sibling) {
                if ($sibling->getId() !== $category->getId()) {
                    $related[] = [
                        'id' => $sibling->getId(),
                        'title' => $sibling->getTitle(),
                        'relationship' => 'sibling',
                    ];
                }
            }
        }

        return $related;
    }

    /**
     * 计算培训持续时间
     * @return array<string, mixed>
     */
    private function calculateTrainingDuration(Category $category): array
    {
        $requirement = $this->requirementService->getCategoryRequirement($category);

        if ($requirement === null) {
            return ['days' => 0, 'hours' => 0];
        }

        $totalHours = $requirement->getInitialTrainingHours();
        $days = ceil($totalHours / 8); // 假设每天8小时

        return [
            'days' => $days,
            'hours' => $totalHours,
            'theory_hours' => $requirement->getTheoryHours(),
            'practice_hours' => $requirement->getPracticeHours(),
        ];
    }

    /**
     * 获取相关资源
     * @return array<string, array<int, mixed>>
     */
    private function getRelatedResources(Category $category): array
    {
        return [
            'documents' => [], // 相关文档
            'videos' => [], // 培训视频
            'materials' => [], // 培训材料
            'tools' => [], // 培训工具
        ];
    }

    /**
     * 生成资格建议
     * @param array<string, mixed> $userInfo
     * @param array<string, mixed> $validationResult
     * @return array<int, string>
     */
    private function generateEligibilityRecommendations(Category $category, array $userInfo, array $validationResult): array
    {
        $recommendations = [];

        if ($validationResult['eligible'] === false) {
            foreach ($validationResult['reasons'] as $reason) {
                if (str_contains($reason, '年龄')) {
                    $recommendations[] = '请确认年龄信息是否正确，或选择适合的培训分类';
                } elseif (str_contains($reason, '学时')) {
                    $recommendations[] = '建议先完成基础培训课程，积累足够的学时';
                } elseif (str_contains($reason, '考试')) {
                    $recommendations[] = '需要通过相关的实操考试才能申请证书';
                } elseif (str_contains($reason, '前置')) {
                    $recommendations[] = '建议先完成前置培训课程';
                }
            }
        } else {
            $recommendations[] = '您已符合该分类的培训要求，可以开始培训';
            
            // 推荐相关培训
            $relatedCategories = $this->getRelatedCategories($category);
            if ($relatedCategories !== []) {
                $recommendations[] = '完成此培训后，您还可以考虑相关的培训分类';
            }
        }

        return array_unique($recommendations);
    }

    /**
     * 同步到课程模块
     */
    private function syncToCourseModule(Category $category): void
    {
        // 这里实现与课程模块的同步逻辑
        // 例如：创建或更新课程分类
    }

    /**
     * 同步到教师模块
     */
    private function syncToTeacherModule(Category $category): void
    {
        // 这里实现与教师模块的同步逻辑
        // 例如：更新教师的授课分类
    }

    /**
     * 同步到证书模块
     */
    private function syncToCertificateModule(Category $category): void
    {
        // 这里实现与证书模块的同步逻辑
        // 例如：创建或更新证书模板
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
} 