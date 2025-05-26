<?php

namespace Tourze\TrainCategoryBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Tourze\TrainCategoryBundle\Entity\Category;

/**
 * 详细分类数据填充（基于真实数据）
 * 
 * 基于job_training_category.sql中的真实数据创建三级分类，包括：
 * - 电工作业的具体分类（低压电工、高压电工、防爆电气等）
 * - 危险化学品安全作业的具体工艺分类
 * - 金属非金属矿山安全作业的具体分类
 * - 主要负责人和安全管理人员的具体行业分类
 */
class CategoryDetailedFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    public static function getGroups(): array
    {
        return ['production', 'dev'];
    }

    public function load(ObjectManager $manager): void
    {
        // 创建主要负责人三级分类
        $this->createMainResponsiblePersonDetails($manager);
        
        // 创建安全生产管理人员三级分类
        $this->createSafetyManagementPersonnelDetails($manager);
        
        // 创建电工作业详细分类
        $this->createElectricalWorkDetails($manager);
        
        // 创建焊接与热切割作业详细分类
        $this->createWeldingCuttingDetails($manager);
        
        // 创建高处作业详细分类
        $this->createHighAltitudeWorkDetails($manager);
        
        // 创建制冷与空调作业详细分类
        $this->createRefrigerationACDetails($manager);
        
        // 创建金属非金属矿山安全作业详细分类
        $this->createMetalNonmetalMineSafetyDetails($manager);
        
        // 创建石油天然气安全作业详细分类
        $this->createOilGasSafetyDetails($manager);
        
        // 创建冶金生产安全作业详细分类
        $this->createMetallurgySafetyDetails($manager);
        
        // 创建危险化学品安全作业详细分类
        $this->createHazmatSafetyDetails($manager);
        
        // 创建烟花爆竹安全作业详细分类
        $this->createFireworksSafetyDetails($manager);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            CategoryFixtures::class,
        ];
    }

    /**
     * 创建主要负责人三级分类
     */
    private function createMainResponsiblePersonDetails(ObjectManager $manager): void
    {
        // 危险化学品主要负责人
        $mainHazardousChemicals = $this->getReference(CategoryFixtures::MAIN_HAZARDOUS_CHEMICALS_REFERENCE, Category::class);
        $this->createSubcategories($manager, $mainHazardousChemicals, [
            ['title' => '生产单位主要负责人', 'sortNumber' => 100],
            ['title' => '经营单位主要负责人', 'sortNumber' => 300],
        ]);

        // 金属非金属矿山主要负责人
        $mainMetalNonmetalMine = $this->getReference(CategoryFixtures::MAIN_METAL_NONMETAL_MINE_REFERENCE, Category::class);
        $this->createSubcategories($manager, $mainMetalNonmetalMine, [
            ['title' => '小型露天采石场', 'sortNumber' => 100],
            ['title' => '露天矿山', 'sortNumber' => 300],
            ['title' => '地下矿山', 'sortNumber' => 500],
        ]);

        // 石油天然气开采主要负责人
        $mainOilGasExtraction = $this->getReference(CategoryFixtures::MAIN_OIL_GAS_EXTRACTION_REFERENCE, Category::class);
        $this->createSubcategories($manager, $mainOilGasExtraction, [
            ['title' => '陆上石油天然气开采主要负责人', 'sortNumber' => 100],
        ]);

        // 烟花爆竹主要负责人
        $mainFireworks = $this->getReference(CategoryFixtures::MAIN_FIREWORKS_REFERENCE, Category::class);
        $this->createSubcategories($manager, $mainFireworks, [
            ['title' => '烟花爆竹经营单位主要负责人', 'sortNumber' => 100],
        ]);

        // 金属冶炼主要负责人
        $mainMetalSmelting = $this->getReference(CategoryFixtures::MAIN_METAL_SMELTING_REFERENCE, Category::class);
        $this->createSubcategories($manager, $mainMetalSmelting, [
            ['title' => '金属冶炼单位主要负责人安全生产', 'sortNumber' => 100],
        ]);

        // 非高危企业主要负责人
        $mainNonHighRisk = $this->getReference(CategoryFixtures::MAIN_NON_HIGH_RISK_REFERENCE, Category::class);
        $this->createSubcategories($manager, $mainNonHighRisk, [
            ['title' => '非高危企业生产经营单位', 'sortNumber' => 100],
        ]);
    }

    /**
     * 创建安全生产管理人员三级分类
     */
    private function createSafetyManagementPersonnelDetails(ObjectManager $manager): void
    {
        // 危险化学品安全管理人员
        $safetyHazardousChemicals = $this->getReference(CategoryFixtures::SAFETY_HAZARDOUS_CHEMICALS_REFERENCE, Category::class);
        $this->createSubcategories($manager, $safetyHazardousChemicals, [
            ['title' => '生产单位安全管理人员', 'sortNumber' => 200],
            ['title' => '经营单位安全管理人员', 'sortNumber' => 400],
        ]);

        // 金属非金属矿山安全管理人员
        $safetyMetalNonmetalMine = $this->getReference(CategoryFixtures::SAFETY_METAL_NONMETAL_MINE_REFERENCE, Category::class);
        $this->createSubcategories($manager, $safetyMetalNonmetalMine, [
            ['title' => '小型露天采石场', 'sortNumber' => 200],
            ['title' => '露天矿山', 'sortNumber' => 400],
            ['title' => '地下矿山', 'sortNumber' => 600],
        ]);

        // 石油天然气开采安全管理人员
        $safetyOilGasExtraction = $this->getReference(CategoryFixtures::SAFETY_OIL_GAS_EXTRACTION_REFERENCE, Category::class);
        $this->createSubcategories($manager, $safetyOilGasExtraction, [
            ['title' => '陆上石油天然气开采安全生产管理人员', 'sortNumber' => 100],
        ]);

        // 烟花爆竹安全管理人员
        $safetyFireworks = $this->getReference(CategoryFixtures::SAFETY_FIREWORKS_REFERENCE, Category::class);
        $this->createSubcategories($manager, $safetyFireworks, [
            ['title' => '烟花爆竹经营单位安全生产管理人员', 'sortNumber' => 100],
        ]);

        // 金属冶炼安全管理人员
        $safetyMetalSmelting = $this->getReference(CategoryFixtures::SAFETY_METAL_SMELTING_REFERENCE, Category::class);
        $this->createSubcategories($manager, $safetyMetalSmelting, [
            ['title' => '金属冶炼单位安全管理人员', 'sortNumber' => 100],
        ]);

        // 非高危企业安全管理人员
        $safetyNonHighRisk = $this->getReference(CategoryFixtures::SAFETY_NON_HIGH_RISK_REFERENCE, Category::class);
        $this->createSubcategories($manager, $safetyNonHighRisk, [
            ['title' => '非高危企业生产经营单位', 'sortNumber' => 100],
        ]);
    }

    /**
     * 创建电工作业详细分类
     */
    private function createElectricalWorkDetails(ObjectManager $manager): void
    {
        $electricalWork = $this->getReference(CategoryFixtures::ELECTRICAL_WORK_REFERENCE, Category::class);

        $details = [
            ['title' => '低压电工作业', 'sortNumber' => 100],
            ['title' => '高压电工作业', 'sortNumber' => 200],
            ['title' => '电力电缆作业', 'sortNumber' => 300],
            ['title' => '继电保护作业', 'sortNumber' => 400],
            ['title' => '电气试验作业', 'sortNumber' => 500],
            ['title' => '防爆电气作业', 'sortNumber' => 600],
        ];

        $this->createSubcategories($manager, $electricalWork, $details);
    }

    /**
     * 创建焊接与热切割作业详细分类
     */
    private function createWeldingCuttingDetails(ObjectManager $manager): void
    {
        $weldingCutting = $this->getReference(CategoryFixtures::WELDING_CUTTING_REFERENCE, Category::class);

        $details = [
            ['title' => '熔化焊接与热切割作业', 'sortNumber' => 100],
            ['title' => '压力焊作业', 'sortNumber' => 200],
            ['title' => '钎焊作业', 'sortNumber' => 300],
        ];

        $this->createSubcategories($manager, $weldingCutting, $details);
    }

    /**
     * 创建高处作业详细分类
     */
    private function createHighAltitudeWorkDetails(ObjectManager $manager): void
    {
        $highAltitudeWork = $this->getReference(CategoryFixtures::HIGH_ALTITUDE_WORK_REFERENCE, Category::class);

        $details = [
            ['title' => '登高架设作业', 'sortNumber' => 100],
            ['title' => '高处安装、维护、拆除作业', 'sortNumber' => 200],
        ];

        $this->createSubcategories($manager, $highAltitudeWork, $details);
    }

    /**
     * 创建制冷与空调作业详细分类
     */
    private function createRefrigerationACDetails(ObjectManager $manager): void
    {
        $refrigerationAC = $this->getReference(CategoryFixtures::REFRIGERATION_AC_REFERENCE, Category::class);

        $details = [
            ['title' => '制冷与空调设备运行操作作业', 'sortNumber' => 100],
            ['title' => '制冷与空调设备安装修理作业', 'sortNumber' => 200],
        ];

        $this->createSubcategories($manager, $refrigerationAC, $details);
    }

    /**
     * 创建金属非金属矿山安全作业详细分类
     */
    private function createMetalNonmetalMineSafetyDetails(ObjectManager $manager): void
    {
        $metalNonmetalMineSafety = $this->getReference(CategoryFixtures::METAL_NONMETAL_MINE_SAFETY_REFERENCE, Category::class);

        $details = [
            ['title' => '金属非金属矿井通风作业', 'sortNumber' => 100],
            ['title' => '尾矿作业', 'sortNumber' => 200],
            ['title' => '安全检查作业(露天矿山)', 'sortNumber' => 300],
            ['title' => '安全检查作业(小型露天采石场)', 'sortNumber' => 400],
            ['title' => '安全检查作业(地下矿山)', 'sortNumber' => 500],
            ['title' => '提升机操作作业', 'sortNumber' => 600],
            ['title' => '支柱作业', 'sortNumber' => 700],
            ['title' => '井下电气作业', 'sortNumber' => 800],
            ['title' => '排水作业', 'sortNumber' => 900],
            ['title' => '爆破作业', 'sortNumber' => 1000],
        ];

        $this->createSubcategories($manager, $metalNonmetalMineSafety, $details);
    }

    /**
     * 创建石油天然气安全作业详细分类
     */
    private function createOilGasSafetyDetails(ObjectManager $manager): void
    {
        $oilGasSafety = $this->getReference(CategoryFixtures::OIL_GAS_SAFETY_REFERENCE, Category::class);

        $details = [
            ['title' => '司钻作业（钻井作业）', 'sortNumber' => 100],
            ['title' => '司钻作业（井下作业）', 'sortNumber' => 200],
        ];

        $this->createSubcategories($manager, $oilGasSafety, $details);
    }

    /**
     * 创建冶金生产安全作业详细分类
     */
    private function createMetallurgySafetyDetails(ObjectManager $manager): void
    {
        $metallurgySafety = $this->getReference(CategoryFixtures::METALLURGY_SAFETY_REFERENCE, Category::class);

        $details = [
            ['title' => '煤气作业', 'sortNumber' => 100],
        ];

        $this->createSubcategories($manager, $metallurgySafety, $details);
    }

    /**
     * 创建危险化学品安全作业详细分类
     */
    private function createHazmatSafetyDetails(ObjectManager $manager): void
    {
        $hazmatSafety = $this->getReference(CategoryFixtures::HAZMAT_SAFETY_REFERENCE, Category::class);

        $details = [
            ['title' => '光气及光气化工艺', 'sortNumber' => 100],
            ['title' => '氯碱电解工艺', 'sortNumber' => 200],
            ['title' => '氯化工艺', 'sortNumber' => 300],
            ['title' => '硝化工艺', 'sortNumber' => 400],
            ['title' => '合成氨工艺', 'sortNumber' => 500],
            ['title' => '裂解(裂化)工艺', 'sortNumber' => 600],
            ['title' => '氟化工艺', 'sortNumber' => 700],
            ['title' => '加氢工艺', 'sortNumber' => 800],
            ['title' => '重氮化工艺', 'sortNumber' => 900],
            ['title' => '氧化工艺', 'sortNumber' => 1000],
            ['title' => '过氧化工艺', 'sortNumber' => 1100],
            ['title' => '胺基化工艺', 'sortNumber' => 1200],
            ['title' => '磺化工艺', 'sortNumber' => 1300],
            ['title' => '聚合工艺', 'sortNumber' => 1400],
            ['title' => '烷基化工艺', 'sortNumber' => 1500],
            ['title' => '化工自动化控制仪表', 'sortNumber' => 1600],
        ];

        $this->createSubcategories($manager, $hazmatSafety, $details);
    }

    /**
     * 创建烟花爆竹安全作业详细分类
     */
    private function createFireworksSafetyDetails(ObjectManager $manager): void
    {
        $fireworksSafety = $this->getReference(CategoryFixtures::FIREWORKS_SAFETY_REFERENCE, Category::class);

        $details = [
            ['title' => '烟花爆竹储存作业', 'sortNumber' => 100],
        ];

        $this->createSubcategories($manager, $fireworksSafety, $details);
    }

    /**
     * 创建子分类的通用方法
     */
    private function createSubcategories(ObjectManager $manager, Category $parent, array $subcategories): void
    {
        foreach ($subcategories as $data) {
            $category = new Category();
            $category->setTitle($data['title']);
            $category->setParent($parent);
            $category->setSortNumber($data['sortNumber']);
            $manager->persist($category);
        }
    }
} 