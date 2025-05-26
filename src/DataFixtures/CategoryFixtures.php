<?php

namespace Tourze\TrainCategoryBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use Tourze\TrainCategoryBundle\Entity\Category;

/**
 * 培训分类数据填充（基于真实数据）
 *
 * 根据job_training_category.sql中的真实数据创建分类结构，包括：
 * - 主要负责人（按行业分类）
 * - 特种作业人员（按作业类型分类）
 * - 安全生产管理人员（按行业分类）
 * - 未分类
 */
class CategoryFixtures extends Fixture implements FixtureGroupInterface
{
    // 主分类引用常量
    public const MAIN_RESPONSIBLE_PERSON_REFERENCE = 'category-main-responsible-person';
    public const SPECIAL_OPERATION_PERSONNEL_REFERENCE = 'category-special-operation-personnel';
    public const SAFETY_MANAGEMENT_PERSONNEL_REFERENCE = 'category-safety-management-personnel';
    public const UNCATEGORIZED_REFERENCE = 'category-uncategorized';

    // 主要负责人子分类引用常量
    public const MAIN_HAZARDOUS_CHEMICALS_REFERENCE = 'category-main-hazardous-chemicals';
    public const MAIN_METAL_NONMETAL_MINE_REFERENCE = 'category-main-metal-nonmetal-mine';
    public const MAIN_OIL_GAS_EXTRACTION_REFERENCE = 'category-main-oil-gas-extraction';
    public const MAIN_FIREWORKS_REFERENCE = 'category-main-fireworks';
    public const MAIN_METAL_SMELTING_REFERENCE = 'category-main-metal-smelting';
    public const MAIN_NON_HIGH_RISK_REFERENCE = 'category-main-non-high-risk';

    // 特种作业人员子分类引用常量
    public const ELECTRICAL_WORK_REFERENCE = 'category-electrical-work';
    public const WELDING_CUTTING_REFERENCE = 'category-welding-cutting';
    public const HIGH_ALTITUDE_WORK_REFERENCE = 'category-high-altitude-work';
    public const REFRIGERATION_AC_REFERENCE = 'category-refrigeration-ac';
    public const METAL_NONMETAL_MINE_SAFETY_REFERENCE = 'category-metal-nonmetal-mine-safety';
    public const OIL_GAS_SAFETY_REFERENCE = 'category-oil-gas-safety';
    public const METALLURGY_SAFETY_REFERENCE = 'category-metallurgy-safety';
    public const HAZMAT_SAFETY_REFERENCE = 'category-hazmat-safety';
    public const FIREWORKS_SAFETY_REFERENCE = 'category-fireworks-safety';

    // 安全生产管理人员子分类引用常量
    public const SAFETY_HAZARDOUS_CHEMICALS_REFERENCE = 'category-safety-hazardous-chemicals';
    public const SAFETY_METAL_NONMETAL_MINE_REFERENCE = 'category-safety-metal-nonmetal-mine';
    public const SAFETY_OIL_GAS_EXTRACTION_REFERENCE = 'category-safety-oil-gas-extraction';
    public const SAFETY_FIREWORKS_REFERENCE = 'category-safety-fireworks';
    public const SAFETY_METAL_SMELTING_REFERENCE = 'category-safety-metal-smelting';
    public const SAFETY_NON_HIGH_RISK_REFERENCE = 'category-safety-non-high-risk';

    public static function getGroups(): array
    {
        return ['production', 'dev'];
    }

    public function load(ObjectManager $manager): void
    {
        // 创建主分类
        $this->createMainCategories($manager);
        
        // 创建主要负责人子分类
        $this->createMainResponsiblePersonSubcategories($manager);
        
        // 创建特种作业人员子分类
        $this->createSpecialOperationPersonnelSubcategories($manager);
        
        // 创建安全生产管理人员子分类
        $this->createSafetyManagementPersonnelSubcategories($manager);

        $manager->flush();
    }

    /**
     * 创建主分类
     */
    private function createMainCategories(ObjectManager $manager): void
    {
        // 主要负责人
        $mainResponsiblePerson = new Category();
        $mainResponsiblePerson->setTitle('主要负责人');
        $mainResponsiblePerson->setSortNumber(1000);
        $manager->persist($mainResponsiblePerson);
        $this->addReference(self::MAIN_RESPONSIBLE_PERSON_REFERENCE, $mainResponsiblePerson);

        // 特种作业人员
        $specialOperationPersonnel = new Category();
        $specialOperationPersonnel->setTitle('特种作业人员');
        $specialOperationPersonnel->setSortNumber(2000);
        $manager->persist($specialOperationPersonnel);
        $this->addReference(self::SPECIAL_OPERATION_PERSONNEL_REFERENCE, $specialOperationPersonnel);

        // 安全生产管理人员
        $safetyManagementPersonnel = new Category();
        $safetyManagementPersonnel->setTitle('安全生产管理人员');
        $safetyManagementPersonnel->setSortNumber(3000);
        $manager->persist($safetyManagementPersonnel);
        $this->addReference(self::SAFETY_MANAGEMENT_PERSONNEL_REFERENCE, $safetyManagementPersonnel);

        // 未分类
        $uncategorized = new Category();
        $uncategorized->setTitle('未分类');
        $uncategorized->setSortNumber(0);
        $manager->persist($uncategorized);
        $this->addReference(self::UNCATEGORIZED_REFERENCE, $uncategorized);
    }

    /**
     * 创建主要负责人子分类
     */
    private function createMainResponsiblePersonSubcategories(ObjectManager $manager): void
    {
        $mainResponsiblePerson = $this->getReference(self::MAIN_RESPONSIBLE_PERSON_REFERENCE, Category::class);

        $subcategories = [
            [
                'title' => '危险化学品',
                'sortNumber' => 900,
                'reference' => self::MAIN_HAZARDOUS_CHEMICALS_REFERENCE,
            ],
            [
                'title' => '金属非金属矿山',
                'sortNumber' => 800,
                'reference' => self::MAIN_METAL_NONMETAL_MINE_REFERENCE,
            ],
            [
                'title' => '石油天然气开采',
                'sortNumber' => 700,
                'reference' => self::MAIN_OIL_GAS_EXTRACTION_REFERENCE,
            ],
            [
                'title' => '烟花爆竹',
                'sortNumber' => 600,
                'reference' => self::MAIN_FIREWORKS_REFERENCE,
            ],
            [
                'title' => '金属冶炼',
                'sortNumber' => 500,
                'reference' => self::MAIN_METAL_SMELTING_REFERENCE,
            ],
            [
                'title' => '非高危企业',
                'sortNumber' => 400,
                'reference' => self::MAIN_NON_HIGH_RISK_REFERENCE,
            ],
        ];

        foreach ($subcategories as $data) {
            $category = new Category();
            $category->setTitle($data['title']);
            $category->setParent($mainResponsiblePerson);
            $category->setSortNumber($data['sortNumber']);
            $manager->persist($category);
            $this->addReference($data['reference'], $category);
        }
    }

    /**
     * 创建特种作业人员子分类
     */
    private function createSpecialOperationPersonnelSubcategories(ObjectManager $manager): void
    {
        $specialOperationPersonnel = $this->getReference(self::SPECIAL_OPERATION_PERSONNEL_REFERENCE, Category::class);

        $subcategories = [
            [
                'title' => '电工作业',
                'sortNumber' => 1000,
                'reference' => self::ELECTRICAL_WORK_REFERENCE,
            ],
            [
                'title' => '焊接与热切割作业',
                'sortNumber' => 900,
                'reference' => self::WELDING_CUTTING_REFERENCE,
            ],
            [
                'title' => '高处作业',
                'sortNumber' => 800,
                'reference' => self::HIGH_ALTITUDE_WORK_REFERENCE,
            ],
            [
                'title' => '制冷与空调作业',
                'sortNumber' => 700,
                'reference' => self::REFRIGERATION_AC_REFERENCE,
            ],
            [
                'title' => '金属非金属矿山安全作业',
                'sortNumber' => 600,
                'reference' => self::METAL_NONMETAL_MINE_SAFETY_REFERENCE,
            ],
            [
                'title' => '石油天然气安全作业',
                'sortNumber' => 500,
                'reference' => self::OIL_GAS_SAFETY_REFERENCE,
            ],
            [
                'title' => '冶金(有色)生产安全作业',
                'sortNumber' => 400,
                'reference' => self::METALLURGY_SAFETY_REFERENCE,
            ],
            [
                'title' => '危险化学品安全作业',
                'sortNumber' => 300,
                'reference' => self::HAZMAT_SAFETY_REFERENCE,
            ],
            [
                'title' => '烟花爆竹安全作业',
                'sortNumber' => 200,
                'reference' => self::FIREWORKS_SAFETY_REFERENCE,
            ],
        ];

        foreach ($subcategories as $data) {
            $category = new Category();
            $category->setTitle($data['title']);
            $category->setParent($specialOperationPersonnel);
            $category->setSortNumber($data['sortNumber']);
            $manager->persist($category);
            $this->addReference($data['reference'], $category);
        }
    }

    /**
     * 创建安全生产管理人员子分类
     */
    private function createSafetyManagementPersonnelSubcategories(ObjectManager $manager): void
    {
        $safetyManagementPersonnel = $this->getReference(self::SAFETY_MANAGEMENT_PERSONNEL_REFERENCE, Category::class);

        $subcategories = [
            [
                'title' => '危险化学品',
                'sortNumber' => 900,
                'reference' => self::SAFETY_HAZARDOUS_CHEMICALS_REFERENCE,
            ],
            [
                'title' => '金属非金属矿山',
                'sortNumber' => 800,
                'reference' => self::SAFETY_METAL_NONMETAL_MINE_REFERENCE,
            ],
            [
                'title' => '石油天然气开采',
                'sortNumber' => 700,
                'reference' => self::SAFETY_OIL_GAS_EXTRACTION_REFERENCE,
            ],
            [
                'title' => '烟花爆竹',
                'sortNumber' => 600,
                'reference' => self::SAFETY_FIREWORKS_REFERENCE,
            ],
            [
                'title' => '金属冶炼',
                'sortNumber' => 500,
                'reference' => self::SAFETY_METAL_SMELTING_REFERENCE,
            ],
            [
                'title' => '非高危企业',
                'sortNumber' => 400,
                'reference' => self::SAFETY_NON_HIGH_RISK_REFERENCE,
            ],
        ];

        foreach ($subcategories as $data) {
            $category = new Category();
            $category->setTitle($data['title']);
            $category->setParent($safetyManagementPersonnel);
            $category->setSortNumber($data['sortNumber']);
            $manager->persist($category);
            $this->addReference($data['reference'], $category);
        }
    }
} 