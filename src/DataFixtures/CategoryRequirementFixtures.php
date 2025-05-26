<?php

namespace Tourze\TrainCategoryBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Tourze\TrainCategoryBundle\Entity\Category;
use Tourze\TrainCategoryBundle\Entity\CategoryRequirement;

/**
 * 分类培训要求数据填充
 *
 * 为培训分类创建符合AQ8011-2023标准的培训要求配置，包括：
 * - 学时要求（初训、复训、理论、实操）
 * - 证书有效期
 * - 考试要求（理论、实操）
 * - 年龄限制
 * - 学历、健康、经验等前置条件
 */
class CategoryRequirementFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    public static function getGroups(): array
    {
        return ['production', 'dev'];
    }

    public function load(ObjectManager $manager): void
    {
        // 创建主要负责人培训要求
        $this->createMainResponsiblePersonRequirements($manager);

        // 创建安全生产管理人员培训要求
        $this->createSafetyManagementPersonnelRequirements($manager);

        // 创建特种作业人员培训要求
        $this->createSpecialOperationPersonnelRequirements($manager);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            CategoryFixtures::class,
        ];
    }

    /**
     * 创建主要负责人培训要求
     */
    private function createMainResponsiblePersonRequirements(ObjectManager $manager): void
    {
        // 主要负责人通用要求
        $mainResponsiblePerson = $this->getReference(
            CategoryFixtures::MAIN_RESPONSIBLE_PERSON_REFERENCE, 
            Category::class
        );
        $this->createRequirement($manager, $mainResponsiblePerson, [
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
            'remarks' => '生产经营单位的主要负责人必须具备与本单位所从事的生产经营活动相应的安全生产知识和管理能力。',
        ]);

        // 危险化学品主要负责人
        $mainHazardousChemicals = $this->getReference(
            CategoryFixtures::MAIN_HAZARDOUS_CHEMICALS_REFERENCE, 
            Category::class
        );
        $this->createRequirement($manager, $mainHazardousChemicals, [
            'initialTrainingHours' => 48,
            'refreshTrainingHours' => 16,
            'theoryHours' => 40,
            'practiceHours' => 8,
            'certificateValidityPeriod' => 36,
            'requiresPracticalExam' => false,
            'requiresOnSiteTraining' => false,
            'minimumAge' => 18,
            'maximumAge' => 65,
            'prerequisites' => ['危险化学品基础知识'],
            'educationRequirements' => ['高中及以上学历'],
            'healthRequirements' => ['身体健康', '无职业禁忌症'],
            'experienceRequirements' => ['具有危险化学品安全管理经验'],
            'remarks' => '危险化学品生产、储存、使用、经营、运输单位的主要负责人安全培训。',
        ]);
    }

    /**
     * 创建安全生产管理人员培训要求
     */
    private function createSafetyManagementPersonnelRequirements(ObjectManager $manager): void
    {
        // 安全生产管理人员通用要求
        $safetyManagementPersonnel = $this->getReference(
            CategoryFixtures::SAFETY_MANAGEMENT_PERSONNEL_REFERENCE, 
            Category::class
        );
        $this->createRequirement($manager, $safetyManagementPersonnel, [
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
            'remarks' => '安全生产管理人员应当按照国家有关规定经安全生产监督管理部门考核合格。',
        ]);

        // 危险化学品安全管理人员
        $safetyHazardousChemicals = $this->getReference(
            CategoryFixtures::SAFETY_HAZARDOUS_CHEMICALS_REFERENCE, 
            Category::class
        );
        $this->createRequirement($manager, $safetyHazardousChemicals, [
            'initialTrainingHours' => 48,
            'refreshTrainingHours' => 16,
            'theoryHours' => 40,
            'practiceHours' => 8,
            'certificateValidityPeriod' => 36,
            'requiresPracticalExam' => false,
            'requiresOnSiteTraining' => false,
            'minimumAge' => 18,
            'maximumAge' => 65,
            'prerequisites' => ['危险化学品安全管理知识'],
            'educationRequirements' => ['中专及以上学历'],
            'healthRequirements' => ['身体健康', '无职业禁忌症'],
            'experienceRequirements' => ['具有危险化学品安全管理经验'],
            'remarks' => '危险化学品单位安全生产管理人员专业培训。',
        ]);
    }

    /**
     * 创建特种作业人员培训要求
     */
    private function createSpecialOperationPersonnelRequirements(ObjectManager $manager): void
    {
        // 特种作业人员通用要求
        $specialOperationPersonnel = $this->getReference(
            CategoryFixtures::SPECIAL_OPERATION_PERSONNEL_REFERENCE, 
            Category::class
        );
        $this->createRequirement($manager, $specialOperationPersonnel, [
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
            'remarks' => '从事特种作业的人员必须接受专门的安全技术培训并考核合格，取得《中华人民共和国特种作业操作证》后，方可上岗作业。',
        ]);

        // 电工作业要求
        $electricalWork = $this->getReference(CategoryFixtures::ELECTRICAL_WORK_REFERENCE, Category::class);
        $this->createRequirement($manager, $electricalWork, [
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
            'remarks' => '对电气设备进行运行、维护、安装、检修、改造、施工、调试等作业。',
        ]);

        // 焊接与热切割作业要求
        $weldingCutting = $this->getReference(CategoryFixtures::WELDING_CUTTING_REFERENCE, Category::class);
        $this->createRequirement($manager, $weldingCutting, [
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
            'remarks' => '运用焊接或者热切割方法对材料进行加工的作业（不含《特种设备安全监察条例》规定的有关作业）。',
        ]);

        // 高处作业要求
        $highAltitudeWork = $this->getReference(CategoryFixtures::HIGH_ALTITUDE_WORK_REFERENCE, Category::class);
        $this->createRequirement($manager, $highAltitudeWork, [
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
            'remarks' => '专门或经常在坠落高度基准面2米及以上有可能坠落的高处进行的作业。',
        ]);

        // 制冷与空调作业要求
        $refrigerationAC = $this->getReference(CategoryFixtures::REFRIGERATION_AC_REFERENCE, Category::class);
        $this->createRequirement($manager, $refrigerationAC, [
            'initialTrainingHours' => 72,
            'refreshTrainingHours' => 24,
            'theoryHours' => 48,
            'practiceHours' => 24,
            'certificateValidityPeriod' => 36,
            'requiresPracticalExam' => true,
            'requiresOnSiteTraining' => true,
            'minimumAge' => 18,
            'maximumAge' => 60,
            'prerequisites' => ['制冷原理基础知识', '电气基础知识'],
            'educationRequirements' => ['初中及以上学历'],
            'healthRequirements' => ['无色盲色弱', '手指灵活', '无呼吸系统疾病'],
            'experienceRequirements' => [],
            'remarks' => '制冷与空调设备运行操作、安装与修理作业。',
        ]);

        // 金属非金属矿山安全作业要求
        $metalNonmetalMineSafety = $this->getReference(CategoryFixtures::METAL_NONMETAL_MINE_SAFETY_REFERENCE, Category::class);
        $this->createRequirement($manager, $metalNonmetalMineSafety, [
            'initialTrainingHours' => 90,
            'refreshTrainingHours' => 24,
            'theoryHours' => 66,
            'practiceHours' => 24,
            'certificateValidityPeriod' => 36,
            'requiresPracticalExam' => true,
            'requiresOnSiteTraining' => true,
            'minimumAge' => 18,
            'maximumAge' => 60,
            'prerequisites' => ['矿山安全基础知识', '地质基础知识'],
            'educationRequirements' => ['初中及以上学历'],
            'healthRequirements' => ['身体健康', '无职业禁忌症', '听力正常'],
            'experienceRequirements' => [],
            'remarks' => '金属非金属矿山井下电气、爆破、提升机操作、支柱作业等作业。',
        ]);

        // 危险化学品安全作业要求
        $hazmatSafety = $this->getReference(CategoryFixtures::HAZMAT_SAFETY_REFERENCE, Category::class);
        $this->createRequirement($manager, $hazmatSafety, [
            'initialTrainingHours' => 84,
            'refreshTrainingHours' => 24,
            'theoryHours' => 60,
            'practiceHours' => 24,
            'certificateValidityPeriod' => 36,
            'requiresPracticalExam' => true,
            'requiresOnSiteTraining' => true,
            'minimumAge' => 18,
            'maximumAge' => 60,
            'prerequisites' => ['化学基础知识', '危险化学品安全知识'],
            'educationRequirements' => ['高中及以上学历'],
            'healthRequirements' => ['身体健康', '无职业禁忌症', '无过敏体质'],
            'experienceRequirements' => [],
            'remarks' => '危险化学品生产、储存装置、设施的运行、维护和检修作业。',
        ]);
    }

    /**
     * 创建培训要求实体
     */
    private function createRequirement(ObjectManager $manager, Category $category, array $data): CategoryRequirement
    {
        $requirement = new CategoryRequirement();
        $requirement->setCategory($category);
        $requirement->setInitialTrainingHours($data['initialTrainingHours']);
        $requirement->setRefreshTrainingHours($data['refreshTrainingHours']);
        $requirement->setTheoryHours($data['theoryHours']);
        $requirement->setPracticeHours($data['practiceHours']);
        $requirement->setCertificateValidityPeriod($data['certificateValidityPeriod']);
        $requirement->setRequiresPracticalExam($data['requiresPracticalExam']);
        $requirement->setRequiresOnSiteTraining($data['requiresOnSiteTraining']);
        $requirement->setMinimumAge($data['minimumAge']);
        $requirement->setMaximumAge($data['maximumAge']);
        $requirement->setPrerequisites($data['prerequisites']);
        $requirement->setEducationRequirements($data['educationRequirements']);
        $requirement->setHealthRequirements($data['healthRequirements']);
        $requirement->setExperienceRequirements($data['experienceRequirements']);
        $requirement->setRemarks($data['remarks']);

        $manager->persist($requirement);

        return $requirement;
    }
} 