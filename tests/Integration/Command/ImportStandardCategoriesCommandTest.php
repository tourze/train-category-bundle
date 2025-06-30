<?php

namespace Tourze\TrainCategoryBundle\Tests\Integration\Command;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Tourze\TrainCategoryBundle\Command\ImportStandardCategoriesCommand;
use Tourze\TrainCategoryBundle\Entity\Category;
use Tourze\TrainCategoryBundle\Service\CategoryService;

class ImportStandardCategoriesCommandTest extends TestCase
{
    private CategoryService $categoryService;
    private ImportStandardCategoriesCommand $command;
    private CommandTester $commandTester;

    protected function setUp(): void
    {
        $this->categoryService = $this->createMock(CategoryService::class);
        $this->command = new ImportStandardCategoriesCommand($this->categoryService);

        $application = new Application();
        $application->add($this->command);

        $this->commandTester = new CommandTester($this->command);
    }

    public function testExecuteDryRun(): void
    {
        $this->commandTester->execute([
            '--dry-run' => true,
        ]);

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('预览模式', $output);
        $this->assertStringContainsString('培训类别', $output);
        $this->assertEquals(Command::SUCCESS, $this->commandTester->getStatusCode());
    }

    public function testExecuteSuccessful(): void
    {
        $this->categoryService
            ->expects($this->atLeastOnce())
            ->method('findByTitle')
            ->willReturn(null);

        $this->categoryService
            ->expects($this->atLeastOnce())
            ->method('createCategory')
            ->willReturn(new Category());

        $this->commandTester->execute([]);

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('标准分类导入完成', $output);
        $this->assertEquals(Command::SUCCESS, $this->commandTester->getStatusCode());
    }

    public function testExecuteWithForceOption(): void
    {
        $existingCategory = new Category();
        
        $this->categoryService
            ->expects($this->atLeastOnce())
            ->method('findByTitle')
            ->willReturn($existingCategory);

        $this->categoryService
            ->expects($this->atLeastOnce())
            ->method('createCategory')
            ->willReturn(new Category());

        $this->commandTester->execute([
            '--force' => true,
        ]);

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('强制模式', $output);
        $this->assertEquals(Command::SUCCESS, $this->commandTester->getStatusCode());
    }

    public function testExecuteWithException(): void
    {
        $this->categoryService
            ->expects($this->once())
            ->method('findByTitle')
            ->willThrowException(new \Exception('Database error'));

        $this->commandTester->execute([]);

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('导入过程中发生错误', $output);
        $this->assertEquals(Command::FAILURE, $this->commandTester->getStatusCode());
    }
}