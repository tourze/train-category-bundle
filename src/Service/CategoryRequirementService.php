<?php

namespace Tourze\TrainCategoryBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Tourze\TrainCategoryBundle\Entity\Category;
use Tourze\TrainCategoryBundle\Entity\CategoryRequirement;
use Tourze\TrainCategoryBundle\Repository\CategoryRequirementRepository;

/**
 * 分类培训要求服务类
 * 
 * 管理培训分类的具体要求，包括学时配置、考试要求、年龄限制等
 */
class CategoryRequirementService
{
    public function __construct(
        private readonly CategoryRequirementRepository $requirementRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * 为分类设置培训要求
     */
    public function setCategoryRequirement(Category $category, array $requirements): CategoryRequirement
    {
        $requirement = $this->requirementRepository->findByCategory($category);
        
        if (!$requirement) {
            $requirement = new CategoryRequirement();
            $requirement->setCategory($category);
        }

        // 设置学时要求
        if (isset($requirements['initialTrainingHours'])) {
            $requirement->setInitialTrainingHours($requirements['initialTrainingHours']);
        }

        if (isset($requirements['refreshTrainingHours'])) {
            $requirement->setRefreshTrainingHours($requirements['refreshTrainingHours']);
        }

        if (isset($requirements['theoryHours'])) {
            $requirement->setTheoryHours($requirements['theoryHours']);
        }

        if (isset($requirements['practiceHours'])) {
            $requirement->setPracticeHours($requirements['practiceHours']);
        }

        // 设置证书要求
        if (isset($requirements['certificateValidityPeriod'])) {
            $requirement->setCertificateValidityPeriod($requirements['certificateValidityPeriod']);
        }

        // 设置考试和培训要求
        if (isset($requirements['requiresPracticalExam'])) {
            $requirement->setRequiresPracticalExam($requirements['requiresPracticalExam']);
        }

        if (isset($requirements['requiresOnSiteTraining'])) {
            $requirement->setRequiresOnSiteTraining($requirements['requiresOnSiteTraining']);
        }

        // 设置年龄要求
        if (isset($requirements['minimumAge'])) {
            $requirement->setMinimumAge($requirements['minimumAge']);
        }

        if (isset($requirements['maximumAge'])) {
            $requirement->setMaximumAge($requirements['maximumAge']);
        }

        // 设置其他要求
        if (isset($requirements['prerequisites'])) {
            $requirement->setPrerequisites($requirements['prerequisites']);
        }

        if (isset($requirements['educationRequirements'])) {
            $requirement->setEducationRequirements($requirements['educationRequirements']);
        }

        if (isset($requirements['healthRequirements'])) {
            $requirement->setHealthRequirements($requirements['healthRequirements']);
        }

        if (isset($requirements['experienceRequirements'])) {
            $requirement->setExperienceRequirements($requirements['experienceRequirements']);
        }

        if (isset($requirements['remarks'])) {
            $requirement->setRemarks($requirements['remarks']);
        }

        // 验证要求的合理性
        $errors = $requirement->validateHours();
        if (!empty($errors)) {
            throw new \InvalidArgumentException('培训要求配置不合理：' . implode('；', $errors));
        }

        $this->entityManager->persist($requirement);
        $this->entityManager->flush();

        return $requirement;
    }

    /**
     * 获取分类的培训要求
     */
    public function getCategoryRequirement(Category $category): ?CategoryRequirement
    {
        return $this->requirementRepository->findByCategory($category);
    }

    /**
     * 验证培训学时是否满足要求
     */
    public function validateTrainingHours(Category $category, int $hours, string $type = 'initial'): bool
    {
        $requirement = $this->getCategoryRequirement($category);
        
        if (!$requirement) {
            return true; // 没有要求则认为满足
        }

        return match ($type) {
            'initial' => $hours >= $requirement->getInitialTrainingHours(),
            'refresh' => $hours >= $requirement->getRefreshTrainingHours(),
            'theory' => $hours >= $requirement->getTheoryHours(),
            'practice' => $hours >= $requirement->getPracticeHours(),
            'total' => $hours >= $requirement->getTotalHours(),
            default => false,
        };
    }

    /**
     * 计算分类的总学时要求
     */
    public function calculateTotalHours(Category $category): int
    {
        $requirement = $this->getCategoryRequirement($category);
        
        return $requirement ? $requirement->getTotalHours() : 0;
    }

    /**
     * 根据类型获取要求
     */
    public function getRequirementsByType(string $type): array
    {
        return match ($type) {
            'practical_exam' => $this->requirementRepository->findRequiringPracticalExam(),
            'onsite_training' => $this->requirementRepository->findRequiringOnSiteTraining(),
            default => [],
        };
    }

    /**
     * 批量设置标准要求
     */
    public function setStandardRequirements(): void
    {
        $standardRequirements = $this->getStandardRequirements();

        foreach ($standardRequirements as $categoryTitle => $requirements) {
            // 这里需要通过CategoryService来查找分类
            // 暂时跳过，等CategoryService完善后再实现
        }
    }

    /**
     * 获取AQ8011-2023标准要求配置
     */
    private function getStandardRequirements(): array
    {
        return [
            '特种作业人员培训' => [
                'initialTrainingHours' => 72,
                'refreshTrainingHours' => 24,
                'theoryHours' => 48,
                'practiceHours' => 24,
                'certificateValidityPeriod' => 36,
                'requiresPracticalExam' => true,
                'requiresOnSiteTraining' => true,
                'minimumAge' => 18,
                'maximumAge' => 60,
                'prerequisites' => ['身体健康', '无妨碍从事相应特种作业的疾病和生理缺陷'],
                'educationRequirements' => ['初中及以上学历'],
                'healthRequirements' => ['体检合格', '无色盲色弱', '听力正常'],
                'experienceRequirements' => [],
            ],
            '生产经营单位主要负责人培训' => [
                'initialTrainingHours' => 48,
                'refreshTrainingHours' => 16,
                'theoryHours' => 40,
                'practiceHours' => 8,
                'certificateValidityPeriod' => 36,
                'requiresPracticalExam' => false,
                'requiresOnSiteTraining' => false,
                'minimumAge' => 18,
                'maximumAge' => 65,
                'prerequisites' => [],
                'educationRequirements' => ['高中及以上学历'],
                'healthRequirements' => ['身体健康'],
                'experienceRequirements' => ['具有相应的安全生产知识和管理能力'],
            ],
            '安全生产管理人员培训' => [
                'initialTrainingHours' => 48,
                'refreshTrainingHours' => 16,
                'theoryHours' => 40,
                'practiceHours' => 8,
                'certificateValidityPeriod' => 36,
                'requiresPracticalExam' => false,
                'requiresOnSiteTraining' => false,
                'minimumAge' => 18,
                'maximumAge' => 65,
                'prerequisites' => [],
                'educationRequirements' => ['中专及以上学历'],
                'healthRequirements' => ['身体健康'],
                'experienceRequirements' => ['具有相应的安全生产知识和管理能力'],
            ],
            '其他从业人员培训' => [
                'initialTrainingHours' => 24,
                'refreshTrainingHours' => 8,
                'theoryHours' => 20,
                'practiceHours' => 4,
                'certificateValidityPeriod' => 24,
                'requiresPracticalExam' => false,
                'requiresOnSiteTraining' => false,
                'minimumAge' => 16,
                'maximumAge' => 65,
                'prerequisites' => [],
                'educationRequirements' => [],
                'healthRequirements' => ['身体健康'],
                'experienceRequirements' => [],
            ],
            // 特种作业类别的具体要求
            '电工作业' => [
                'initialTrainingHours' => 80,
                'refreshTrainingHours' => 24,
                'theoryHours' => 56,
                'practiceHours' => 24,
                'certificateValidityPeriod' => 36,
                'requiresPracticalExam' => true,
                'requiresOnSiteTraining' => true,
                'minimumAge' => 18,
                'maximumAge' => 60,
                'prerequisites' => ['电工基础知识', '安全用电知识'],
                'educationRequirements' => ['初中及以上学历'],
                'healthRequirements' => ['无色盲色弱', '手指灵活', '听力正常'],
                'experienceRequirements' => [],
            ],
            '焊接与热切割作业' => [
                'initialTrainingHours' => 80,
                'refreshTrainingHours' => 24,
                'theoryHours' => 56,
                'practiceHours' => 24,
                'certificateValidityPeriod' => 36,
                'requiresPracticalExam' => true,
                'requiresOnSiteTraining' => true,
                'minimumAge' => 18,
                'maximumAge' => 60,
                'prerequisites' => ['焊接基础知识', '金属材料知识'],
                'educationRequirements' => ['初中及以上学历'],
                'healthRequirements' => ['视力正常', '手部灵活', '无呼吸系统疾病'],
                'experienceRequirements' => [],
            ],
            '高处作业' => [
                'initialTrainingHours' => 72,
                'refreshTrainingHours' => 24,
                'theoryHours' => 48,
                'practiceHours' => 24,
                'certificateValidityPeriod' => 36,
                'requiresPracticalExam' => true,
                'requiresOnSiteTraining' => true,
                'minimumAge' => 18,
                'maximumAge' => 55,
                'prerequisites' => ['高处作业安全知识', '防护用品使用'],
                'educationRequirements' => ['初中及以上学历'],
                'healthRequirements' => ['无恐高症', '无心脏病', '无高血压', '平衡感良好'],
                'experienceRequirements' => [],
            ],
        ];
    }

    /**
     * 检查用户是否满足分类要求
     */
    public function checkUserEligibility(Category $category, array $userInfo): array
    {
        $requirement = $this->getCategoryRequirement($category);
        
        if (!$requirement) {
            return ['eligible' => true, 'reasons' => []];
        }

        $reasons = [];

        // 检查年龄要求
        if (isset($userInfo['age'])) {
            if (!$requirement->checkAgeRequirement($userInfo['age'])) {
                $reasons[] = "年龄不符合要求（要求：{$requirement->getMinimumAge()}-{$requirement->getMaximumAge()}岁）";
            }
        }

        // 检查学历要求
        $educationRequirements = $requirement->getEducationRequirements();
        if (!empty($educationRequirements) && isset($userInfo['education'])) {
            // 这里可以实现具体的学历验证逻辑
        }

        // 检查健康要求
        $healthRequirements = $requirement->getHealthRequirements();
        if (!empty($healthRequirements) && isset($userInfo['health'])) {
            // 这里可以实现具体的健康状况验证逻辑
        }

        // 检查前置条件
        $prerequisites = $requirement->getPrerequisites();
        if (!empty($prerequisites) && isset($userInfo['prerequisites'])) {
            // 这里可以实现具体的前置条件验证逻辑
        }

        return [
            'eligible' => empty($reasons),
            'reasons' => $reasons,
            'requirement' => $requirement->getRequirementSummary(),
        ];
    }

    /**
     * 获取要求统计信息
     */
    public function getRequirementStatistics(): array
    {
        return $this->requirementRepository->getRequirementStatistics();
    }

    /**
     * 删除分类要求
     */
    public function deleteCategoryRequirement(Category $category): void
    {
        $requirement = $this->getCategoryRequirement($category);
        
        if ($requirement) {
            $this->entityManager->remove($requirement);
            $this->entityManager->flush();
        }
    }
} 