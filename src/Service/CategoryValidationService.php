<?php

namespace Tourze\TrainCategoryBundle\Service;

use Tourze\TrainCategoryBundle\Entity\Category;

/**
 * 分类验证服务类
 *
 * 提供分类结构、要求配置、用户资格等各种验证功能
 */
class CategoryValidationService
{
    public function __construct(
        private readonly CategoryService $categoryService,
        private readonly CategoryRequirementService $requirementService,
    ) {
    }

    /**
     * 验证分类结构的完整性
     * @return array<int, string>
     */
    public function validateCategoryStructure(Category $category): array
    {
        $errors = [];

        // 基础验证
        $basicErrors = $this->categoryService->validateCategoryStructure($category);
        $errors = array_merge($errors, $basicErrors);

        // 验证层级深度
        $depth = $this->calculateCategoryDepth($category);
        if ($depth > 5) {
            $errors[] = '分类层级过深，建议不超过5级';
        }

        // 验证子分类数量
        $childrenCount = $category->getChildren()->count();
        if ($childrenCount > 20) {
            $errors[] = '子分类数量过多，建议不超过20个';
        }

        // 验证分类路径长度
        $path = $this->categoryService->getCategoryPath($category);
        $pathString = implode('/', array_map(fn($cat) => $cat instanceof Category ? $cat->getTitle() : '', $path));
        if (strlen($pathString) > 200) {
            $errors[] = '分类路径过长，建议控制在200字符以内';
        }

        /** @var array<int, string> */
        return $errors;
    }

    /**
     * 验证教师资质是否符合分类要求
     * @param array<string, mixed> $teacherInfo
     * @return array<int, string>
     */
    public function validateTeacherQualification(Category $category, array $teacherInfo): array
    {
        $errors = [];
        $requirement = $this->requirementService->getCategoryRequirement($category);

        if ($requirement === null) {
            return $errors; // 没有要求则通过验证
        }

        // 验证年龄要求
        if (isset($teacherInfo['age']) && is_int($teacherInfo['age'])) {
            if (!$requirement->checkAgeRequirement($teacherInfo['age'])) {
                $errors[] = "教师年龄不符合要求（要求：{$requirement->getMinimumAge()}-{$requirement->getMaximumAge()}岁）";
            }
        }

        // 验证学历要求
        if (isset($teacherInfo['education']) && is_string($teacherInfo['education'])) {
            $educationRequirements = $requirement->getEducationRequirements();
            if (!empty($educationRequirements)) {
                $isEducationValid = $this->validateEducationRequirement(
                    $teacherInfo['education'],
                    $educationRequirements
                );
                if (!$isEducationValid) {
                    $errors[] = '教师学历不符合要求：' . implode('、', $educationRequirements);
                }
            }
        }

        // 验证工作经验
        if (isset($teacherInfo['experience']) && is_array($teacherInfo['experience'])) {
            $experienceRequirements = $requirement->getExperienceRequirements();
            if (!empty($experienceRequirements)) {
                /** @var array<string, mixed> $experience */
                $experience = $teacherInfo['experience'];
                $isExperienceValid = $this->validateExperienceRequirement(
                    $experience,
                    $experienceRequirements
                );
                if (!$isExperienceValid) {
                    $errors[] = '教师工作经验不符合要求';
                }
            }
        }

        // 验证健康状况
        if (isset($teacherInfo['health']) && is_array($teacherInfo['health'])) {
            $healthRequirements = $requirement->getHealthRequirements();
            if (!empty($healthRequirements)) {
                /** @var array<string, mixed> $health */
                $health = $teacherInfo['health'];
                $isHealthValid = $this->validateHealthRequirement(
                    $health,
                    $healthRequirements
                );
                if (!$isHealthValid) {
                    $errors[] = '教师健康状况不符合要求：' . implode('、', $healthRequirements);
                }
            }
        }

        return $errors;
    }

    /**
     * 验证培训要求配置的合理性
     * @param array<string, mixed> $trainingData
     * @return array<int, string>
     */
    public function validateTrainingRequirements(Category $category, array $trainingData): array
    {
        $errors = [];
        $requirement = $this->requirementService->getCategoryRequirement($category);

        if ($requirement === null) {
            return $errors; // 没有要求则通过验证
        }

        // 验证学时配置
        if (isset($trainingData['hours']) && is_int($trainingData['hours'])) {
            $hours = $trainingData['hours'];
            $typeValue = $trainingData['type'] ?? 'initial';
            $type = is_string($typeValue) ? $typeValue : 'initial';

            if (!$this->requirementService->validateTrainingHours($category, $hours, $type)) {
                $requiredHours = match ($type) {
                    'initial' => $requirement->getInitialTrainingHours(),
                    'refresh' => $requirement->getRefreshTrainingHours(),
                    'theory' => $requirement->getTheoryHours(),
                    'practice' => $requirement->getPracticeHours(),
                    'total' => $requirement->getTotalHours(),
                    default => 0,
                };
                $errors[] = sprintf('培训学时不足，要求至少%d学时，实际%d学时', $requiredHours, $hours);
            }
        }

        // 验证实操考试要求
        if ($requirement->isRequiresPracticalExam()) {
            if (!isset($trainingData['practicalExam']) || $trainingData['practicalExam'] === false) {
                $errors[] = '该分类要求进行实操考试';
            }
        }

        // 验证现场培训要求
        if ($requirement->isRequiresOnSiteTraining()) {
            if (!isset($trainingData['onSiteTraining']) || $trainingData['onSiteTraining'] === false) {
                $errors[] = '该分类要求进行现场培训';
            }
        }

        return $errors;
    }

    /**
     * 检查证书申请资格
     * @param array<string, mixed> $userInfo
     * @return array<string, mixed>
     */
    public function checkCertificateEligibility(Category $category, array $userInfo): array
    {
        $result = [
            'eligible' => true,
            'reasons' => [],
            'requirements' => [],
        ];

        $requirement = $this->requirementService->getCategoryRequirement($category);

        if ($requirement === null) {
            return $result;
        }

        // 检查用户资格
        $eligibilityCheck = $this->requirementService->checkUserEligibility($category, $userInfo);
        if (!$eligibilityCheck['eligible']) {
            $result['eligible'] = false;
            $reasons = $eligibilityCheck['reasons'];
            if (is_array($reasons)) {
                $result['reasons'] = array_merge($result['reasons'], $reasons);
            }
        }

        // 检查培训完成情况
        if (isset($userInfo['trainingHours']) && is_numeric($userInfo['trainingHours'])) {
            $requiredHours = $requirement->getInitialTrainingHours();
            $completedHours = (int) $userInfo['trainingHours'];
            if ($completedHours < $requiredHours) {
                $result['eligible'] = false;
                $result['reasons'][] = sprintf('培训学时不足，需要%d学时，已完成%d学时', $requiredHours, $completedHours);
            }
        }

        // 检查考试成绩
        if ($requirement->isRequiresPracticalExam()) {
            if (!isset($userInfo['practicalExamPassed']) || $userInfo['practicalExamPassed'] === false) {
                $result['eligible'] = false;
                $result['reasons'][] = '需要通过实操考试';
            }
        }

        // 添加要求说明
        $result['requirements'] = [
            'summary' => $requirement->getRequirementSummary(),
            'initialHours' => $requirement->getInitialTrainingHours(),
            'practicalExam' => $requirement->isRequiresPracticalExam(),
            'onSiteTraining' => $requirement->isRequiresOnSiteTraining(),
            'ageRange' => [$requirement->getMinimumAge(), $requirement->getMaximumAge()],
            'validityPeriod' => $requirement->getCertificateValidityPeriod(),
        ];

        return $result;
    }

    /**
     * 验证分类的AQ8011-2023标准符合性
     * @return array<string, mixed>
     */
    public function validateStandardCompliance(Category $category): array
    {
        $errors = [];
        $warnings = [];

        // 检查分类命名是否符合标准
        /** @var array<string, mixed> $standardCategories */
        $standardCategories = $this->categoryService->getStandardizedCategories();
        $isStandardCategory = $this->isStandardCategory($category, $standardCategories);

        if (!$isStandardCategory) {
            $warnings[] = '分类名称不在AQ8011-2023标准分类中，建议使用标准分类名称';
        }

        // 检查是否有对应的培训要求
        $requirement = $this->requirementService->getCategoryRequirement($category);
        if ($requirement === null) {
            $errors[] = '缺少培训要求配置，不符合AQ8011-2023标准';
        } else {
            // 验证要求配置的合理性
            $requirementErrors = $requirement->validateHours();
            $errors = array_merge($errors, $requirementErrors);

            // 检查特种作业的特殊要求
            if (str_contains($category->getTitle(), '特种作业') || 
                $this->isSpecialOperationCategory($category)) {
                
                if (!$requirement->isRequiresPracticalExam()) {
                    $errors[] = '特种作业分类必须要求实操考试';
                }

                if ($requirement->getInitialTrainingHours() < 72) {
                    $errors[] = '特种作业初训学时不得少于72学时';
                }

                if ($requirement->getCertificateValidityPeriod() > 36) {
                    $warnings[] = '特种作业证书有效期建议不超过36个月';
                }
            }
        }

        return [
            'compliant' => empty($errors),
            'errors' => $errors,
            'warnings' => $warnings,
        ];
    }

    /**
     * 批量验证分类结构
     * @param array<int, Category> $categories
     * @return array<int, array<string, mixed>>
     */
    public function batchValidateCategories(array $categories): array
    {
        $results = [];

        foreach ($categories as $category) {
            if (!$category instanceof Category) {
                continue;
            }

            $categoryId = $category->getId();
            if ($categoryId !== null) {
                $results[$categoryId] = [
                    'category' => $category->getTitle(),
                    'structure' => $this->validateCategoryStructure($category),
                    'standard' => $this->validateStandardCompliance($category),
                ];
            }
        }

        /** @var array<int, array<string, mixed>> */
        $numericKeyResults = [];
        $index = 0;
        foreach ($results as $result) {
            $numericKeyResults[$index] = $result;
            $index++;
        }

        return $numericKeyResults;
    }

    /**
     * 计算分类深度
     */
    private function calculateCategoryDepth(Category $category): int
    {
        $depth = 1;
        $current = $category;

        while ($current->getParent() !== null) {
            $depth++;
            $current = $current->getParent();
        }

        return $depth;
    }

    /**
     * 验证学历要求
     * @param array<int, string> $requirements
     */
    private function validateEducationRequirement(string $userEducation, array $requirements): bool
    {
        // 简化的学历验证逻辑
        $educationLevels = [
            '小学' => 1,
            '初中' => 2,
            '高中' => 3,
            '中专' => 3,
            '大专' => 4,
            '本科' => 5,
            '硕士' => 6,
            '博士' => 7,
        ];

        $userLevel = $educationLevels[$userEducation] ?? 0;

        foreach ($requirements as $requirement) {
            $requiredLevel = $educationLevels[$requirement] ?? 0;
            if ($userLevel >= $requiredLevel) {
                return true;
            }
        }

        return false;
    }

    /**
     * 验证工作经验要求
     * @param array<string, mixed> $userExperience
     * @param array<int, string> $requirements
     */
    private function validateExperienceRequirement(array $userExperience, array $requirements): bool
    {
        // 这里可以实现具体的工作经验验证逻辑
        // 暂时返回true
        return true;
    }

    /**
     * 验证健康要求
     * @param array<string, mixed> $userHealth
     * @param array<int, string> $requirements
     */
    private function validateHealthRequirement(array $userHealth, array $requirements): bool
    {
        // 这里可以实现具体的健康状况验证逻辑
        // 暂时返回true
        return true;
    }

    /**
     * 检查是否为标准分类
     * @param array<string, mixed> $standardCategories
     */
    private function isStandardCategory(Category $category, array $standardCategories): bool
    {
        foreach ($standardCategories as $parentTitle => $children) {
            if ($category->getTitle() === $parentTitle) {
                return true;
            }

            if (is_array($children)) {
                foreach ($children as $childTitle) {
                    if ($category->getTitle() === $childTitle) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * 检查是否为特种作业分类
     */
    private function isSpecialOperationCategory(Category $category): bool
    {
        $specialOperations = [
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
        ];

        return in_array($category->getTitle(), $specialOperations) ||
               ($category->getParent() !== null && $category->getParent()->getTitle() === '特种作业类别');
    }
}
