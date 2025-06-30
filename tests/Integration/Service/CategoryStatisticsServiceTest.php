<?php

namespace Tourze\TrainCategoryBundle\Tests\Integration\Service;

use PHPUnit\Framework\TestCase;
use Tourze\TrainCategoryBundle\Repository\CategoryRepository;
use Tourze\TrainCategoryBundle\Repository\CategoryRequirementRepository;
use Tourze\TrainCategoryBundle\Service\CategoryStatisticsService;

class CategoryStatisticsServiceTest extends TestCase
{
    private CategoryRepository $categoryRepository;
    private CategoryRequirementRepository $requirementRepository;
    private CategoryStatisticsService $service;

    protected function setUp(): void
    {
        $this->categoryRepository = $this->createMock(CategoryRepository::class);
        $this->requirementRepository = $this->createMock(CategoryRequirementRepository::class);
        $this->service = new CategoryStatisticsService(
            $this->categoryRepository,
            $this->requirementRepository
        );
    }

    public function testServiceExists(): void
    {
        $this->assertInstanceOf(CategoryStatisticsService::class, $this->service);
    }
}