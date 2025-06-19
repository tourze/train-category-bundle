<?php

namespace Tourze\TrainCategoryBundle\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Tourze\TrainCategoryBundle\Service\CategoryService;

/**
 * 导入AQ8011-2023标准分类命令
 */
#[AsCommand(
    name: self::NAME,
    description: '导入AQ8011-2023标准培训分类'
)]
class ImportStandardCategoriesCommand extends Command
{
    public const NAME = 'train-category:import-standard';
    
public function __construct(
        private readonly CategoryService $categoryService,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('导入AQ8011-2023标准培训分类')
            ->setHelp('此命令将导入符合AQ8011-2023标准的培训分类结构')
            ->addOption(
                'force',
                'f',
                InputOption::VALUE_NONE,
                '强制导入，即使分类已存在也会重新创建'
            )
            ->addOption(
                'dry-run',
                null,
                InputOption::VALUE_NONE,
                '预览模式，只显示将要导入的分类，不实际执行'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        $force = (bool) $input->getOption('force');
        $dryRun = (bool) $input->getOption('dry-run');

        $io->title('AQ8011-2023标准培训分类导入工具');

        // 获取标准分类数据
        $standardCategories = $this->getAQ8011StandardCategories();

        if ((bool) $dryRun) {
            $io->section('预览模式 - 将要导入的分类：');
            $this->previewCategories($io, $standardCategories);
            return Command::SUCCESS;
        }

        $io->section('开始导入标准分类...');

        try {
            $importedCount = 0;
            $skippedCount = 0;

            foreach ($standardCategories as $parentTitle => $children) {
                $io->writeln("处理分类组: <info>{$parentTitle}</info>");

                // 检查父分类是否存在
                $existingParent = $this->categoryService->findByTitle($parentTitle);
                
                if ($existingParent !== null && !$force) {
                    $io->writeln("  跳过已存在的父分类: {$parentTitle}");
                    $skippedCount++;
                } else {
                    if ($existingParent !== null && $force) {
                        $io->writeln("  强制模式：重新创建父分类: {$parentTitle}");
                    }
                    
                    $parent = $this->categoryService->createCategory($parentTitle, null, 1000);
                    $importedCount++;
                    $io->writeln("  ✓ 创建父分类: {$parentTitle}");
                }

                // 处理子分类
                if (!is_array($children)) {
                    continue;
                }
                foreach ($children as $index => $childData) {
                    $childTitle = is_array($childData) ? (string) $childData['title'] : (string) $childData;
                    $childCode = is_array($childData) ? ($childData['code'] ?? null) : null;
                    $childDescription = is_array($childData) ? ($childData['description'] ?? null) : null;

                    $parent = $this->categoryService->findByTitle($parentTitle);
                    $existingChild = $this->categoryService->findByTitleAndParent($childTitle, $parent);

                    if ($existingChild !== null && !$force) {
                        $io->writeln("    跳过已存在的子分类: " . $childTitle);
                        $skippedCount++;
                    } else {
                        if ($existingChild !== null && $force) {
                            $io->writeln("    强制模式：重新创建子分类: " . $childTitle);
                        }

                        $sortNumber = 1000 - (int) $index;
                        $child = $this->categoryService->createCategory($childTitle, $parent, $sortNumber);
                        
                        // 如果有编码和描述，可以在这里扩展实体来存储
                        if (!empty($childCode) || !empty($childDescription)) {
                            $io->writeln("    编码: " . ($childCode ?? '') . ", 描述: " . ($childDescription ?? ''), OutputInterface::VERBOSITY_VERBOSE);
                        }

                        $importedCount++;
                        $io->writeln("    ✓ 创建子分类: " . $childTitle);
                    }
                }

                $io->newLine();
            }

            $io->success([
                '标准分类导入完成！',
                "导入分类数量: {$importedCount}",
                "跳过分类数量: {$skippedCount}",
            ]);

        } catch (\Throwable $e) {
            $io->error([
                '导入过程中发生错误：',
                $e->getMessage()
            ]);
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    /**
     * 获取AQ8011-2023标准分类数据
     * @return array<string, array<int, array<string, string>|string>>
     */
    private function getAQ8011StandardCategories(): array
    {
        return [
            '培训类别' => [
                [
                    'title' => '特种作业人员培训',
                    'code' => 'SPECIAL_OPERATION',
                    'description' => '从事特种作业的人员必须接受的专门培训'
                ],
                [
                    'title' => '生产经营单位主要负责人培训',
                    'code' => 'MAIN_RESPONSIBLE_PERSON',
                    'description' => '生产经营单位主要负责人的安全生产培训'
                ],
                [
                    'title' => '安全生产管理人员培训',
                    'code' => 'SAFETY_MANAGER',
                    'description' => '安全生产管理人员的专业培训'
                ],
                [
                    'title' => '其他从业人员培训',
                    'code' => 'OTHER_EMPLOYEES',
                    'description' => '除特种作业人员外的其他从业人员培训'
                ],
            ],
            '行业分类' => [
                [
                    'title' => '矿山行业',
                    'code' => 'MINING',
                    'description' => '煤矿、非煤矿山等采矿行业'
                ],
                [
                    'title' => '危险化学品行业',
                    'code' => 'HAZARDOUS_CHEMICALS',
                    'description' => '危险化学品生产、储存、使用、经营、运输行业'
                ],
                [
                    'title' => '石油天然气开采行业',
                    'code' => 'OIL_GAS_EXTRACTION',
                    'description' => '石油天然气勘探开发行业'
                ],
                [
                    'title' => '金属冶炼行业',
                    'code' => 'METAL_SMELTING',
                    'description' => '黑色金属、有色金属冶炼行业'
                ],
                [
                    'title' => '建筑施工行业',
                    'code' => 'CONSTRUCTION',
                    'description' => '房屋建筑、市政工程等建筑施工行业'
                ],
                [
                    'title' => '道路运输行业',
                    'code' => 'ROAD_TRANSPORT',
                    'description' => '道路旅客运输、货物运输行业'
                ],
                [
                    'title' => '其他行业',
                    'code' => 'OTHER_INDUSTRIES',
                    'description' => '其他需要安全生产培训的行业'
                ],
            ],
            '特种作业类别' => [
                [
                    'title' => '电工作业',
                    'code' => 'ELECTRICAL_WORK',
                    'description' => '对电气设备进行运行、维护、安装、检修、改造、施工、调试等作业'
                ],
                [
                    'title' => '焊接与热切割作业',
                    'code' => 'WELDING_CUTTING',
                    'description' => '运用焊接或者热切割方法对材料进行加工的作业'
                ],
                [
                    'title' => '高处作业',
                    'code' => 'HIGH_ALTITUDE_WORK',
                    'description' => '专门或经常在坠落高度基准面2米及以上有可能坠落的高处进行的作业'
                ],
                [
                    'title' => '制冷与空调作业',
                    'code' => 'REFRIGERATION_AC',
                    'description' => '制冷与空调设备运行操作、安装与修理作业'
                ],
                [
                    'title' => '煤矿安全作业',
                    'code' => 'COAL_MINE_SAFETY',
                    'description' => '煤矿井下电气、爆破、安全监测监控、瓦斯检查、安全检查、提升机操作、采煤机操作、掘进机操作、瓦斯抽采、防突等作业'
                ],
                [
                    'title' => '金属非金属矿山安全作业',
                    'code' => 'METAL_NONMETAL_MINE',
                    'description' => '金属非金属矿山井下电气、爆破、提升机操作、支柱作业等作业'
                ],
                [
                    'title' => '石油天然气安全作业',
                    'code' => 'OIL_GAS_SAFETY',
                    'description' => '石油天然气钻井司钻、井架工、天车工、链钳工、井控装置操作工等作业'
                ],
                [
                    'title' => '冶金（有色）生产安全作业',
                    'code' => 'METALLURGY_SAFETY',
                    'description' => '冶金、有色金属行业煤气生产、煤气储存输送、煤气使用、冶金煤气设施检修维护、高温熔融金属吊运等作业'
                ],
                [
                    'title' => '危险化学品安全作业',
                    'code' => 'HAZMAT_SAFETY',
                    'description' => '危险化学品生产、储存装置、设施的运行、维护和检修作业'
                ],
                [
                    'title' => '烟花爆竹安全作业',
                    'code' => 'FIREWORKS_SAFETY',
                    'description' => '烟花爆竹生产、储存中的药物混合、造粒、筛选、装药、筑药、压药、搬运等危险工序的作业'
                ],
            ],
        ];
    }

    /**
     * 预览将要导入的分类
     * @param array<string, array<int, array<string, string>|string>> $categories
     */
    private function previewCategories(SymfonyStyle $io, array $categories): void
    {
        foreach ($categories as $parentTitle => $children) {
            $io->writeln("📁 <info>{$parentTitle}</info>");
            
            if (!is_array($children)) {
                continue;
            }
            foreach ($children as $index => $childData) {
                $childTitle = is_array($childData) ? (string) $childData['title'] : (string) $childData;
                $childCode = is_array($childData) ? ($childData['code'] ?? '') : '';
                $childDescription = is_array($childData) ? ($childData['description'] ?? '') : '';
                
                $io->writeln("  ├── " . $childTitle);
                if ((bool) $childCode) {
                    $io->writeln("      编码: " . $childCode, OutputInterface::VERBOSITY_VERBOSE);
                }
                if ((bool) $childDescription) {
                    $io->writeln("      描述: " . $childDescription, OutputInterface::VERBOSITY_VERBOSE);
                }
            }
            $io->newLine();
        }
    }
} 