<?php

namespace Tourze\TrainCategoryBundle\Tests\Integration\Service;

use PHPUnit\Framework\TestCase;
use Tourze\TrainCategoryBundle\Repository\CategoryRepository;
use Tourze\TrainCategoryBundle\Repository\CategoryRequirementRepository;
use Tourze\TrainCategoryBundle\Service\CategorySearchService;

class CategorySearchServiceTest extends TestCase
{
    private CategoryRepository $categoryRepository;
    private CategoryRequirementRepository $requirementRepository;
    private CategorySearchService $service;

    protected function setUp(): void
    {
        $this->categoryRepository = $this->createMock(CategoryRepository::class);
        $this->requirementRepository = $this->createMock(CategoryRequirementRepository::class);
        $this->service = new CategorySearchService(
            $this->categoryRepository,
            $this->requirementRepository
        );
    }

    public function testServiceExists(): void
    {
        $this->assertInstanceOf(CategorySearchService::class, $this->service);
    }
}