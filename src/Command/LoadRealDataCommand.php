<?php

namespace Tourze\TrainCategoryBundle\Command;

use Doctrine\Bundle\FixturesBundle\Loader\SymfonyFixturesLoader;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Tourze\TrainCategoryBundle\DataFixtures\CategoryDetailedFixtures;
use Tourze\TrainCategoryBundle\DataFixtures\CategoryFixtures;
use Tourze\TrainCategoryBundle\DataFixtures\CategoryRequirementFixtures;

/**
 * 加载基于真实数据的培训分类
 *
 * 该命令会加载基于job_training_category.sql真实数据的分类结构，
 * 包括主要负责人、特种作业人员、安全生产管理人员三大分类及其子分类。
 */
#[AsCommand(
    name: 'train-category:load-real-data',
    description: '加载基于真实SQL数据的培训分类'
)]
class LoadRealDataCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly SymfonyFixturesLoader $fixturesLoader
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('加载基于真实SQL数据的培训分类')
            ->setHelp('该命令会清空现有的培训分类数据，然后加载基于job_training_category.sql的真实数据结构。')
            ->addOption(
                'append',
                null,
                InputOption::VALUE_NONE,
                '追加数据而不是清空现有数据'
            )
            ->addOption(
                'with-requirements',
                null,
                InputOption::VALUE_NONE,
                '同时加载培训要求数据'
            )
            ->addOption(
                'detailed',
                null,
                InputOption::VALUE_NONE,
                '加载详细的三级分类数据'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('加载基于真实数据的培训分类');

        // 准备要加载的Fixture
        $fixtures = [];
        
        // 基础分类数据
        $fixtures[] = new CategoryFixtures();
        
        // 详细分类数据
        if ($input->getOption('detailed')) {
            $fixtures[] = new CategoryDetailedFixtures();
        }
        
        // 培训要求数据
        if ($input->getOption('with-requirements')) {
            $fixtures[] = new CategoryRequirementFixtures();
        }

        // 加载Fixture
        foreach ($fixtures as $fixture) {
            $this->fixturesLoader->addFixture($fixture);
        }

        // 配置执行器
        $purger = new ORMPurger($this->entityManager);
        $executor = new ORMExecutor($this->entityManager, $purger);

        $io->section('开始加载数据...');

        try {
            // 执行数据加载
            $executor->execute(
                $this->fixturesLoader->getFixtures(),
                $input->getOption('append')
            );

            $io->success('数据加载完成！');

            // 显示加载的数据统计
            $this->showStatistics($io);

        } catch (\Exception $e) {
            $io->error('数据加载失败：' . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    /**
     * 显示加载的数据统计
     */
    private function showStatistics(SymfonyStyle $io): void
    {
        $io->section('数据统计');

        // 查询分类统计
        $categoryRepo = $this->entityManager->getRepository(\Tourze\TrainCategoryBundle\Entity\Category::class);
        
        $totalCategories = $categoryRepo->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->getQuery()
            ->getSingleScalarResult();

        $rootCategories = $categoryRepo->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->where('c.parent IS NULL')
            ->getQuery()
            ->getSingleScalarResult();

        $secondLevelCategories = $categoryRepo->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->join('c.parent', 'p')
            ->where('p.parent IS NULL')
            ->getQuery()
            ->getSingleScalarResult();

        $thirdLevelCategories = $categoryRepo->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->join('c.parent', 'p')
            ->join('p.parent', 'pp')
            ->where('pp.parent IS NULL')
            ->getQuery()
            ->getSingleScalarResult();

        // 查询培训要求统计
        $requirementRepo = $this->entityManager->getRepository(\Tourze\TrainCategoryBundle\Entity\CategoryRequirement::class);
        $totalRequirements = $requirementRepo->createQueryBuilder('r')
            ->select('COUNT(r.id)')
            ->getQuery()
            ->getSingleScalarResult();

        $io->table(
            ['项目', '数量'],
            [
                ['总分类数', $totalCategories],
                ['一级分类', $rootCategories],
                ['二级分类', $secondLevelCategories],
                ['三级分类', $thirdLevelCategories],
                ['培训要求', $totalRequirements],
            ]
        );

        // 显示主分类列表
        $mainCategories = $categoryRepo->findBy(['parent' => null], ['sortNumber' => 'ASC']);
        
        if (!empty($mainCategories)) {
            $io->section('主分类列表');
            $rows = [];
            foreach ($mainCategories as $category) {
                $childCount = count($category->getChildren());
                $rows[] = [
                    $category->getTitle(),
                    $category->getSortNumber(),
                    $childCount,
                ];
            }
            
            $io->table(
                ['分类名称', '排序号', '子分类数'],
                $rows
            );
        }
    }
} 