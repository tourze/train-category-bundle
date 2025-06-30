<?php

namespace Tourze\TrainCategoryBundle\Tests\Integration\Service;

use PHPUnit\Framework\TestCase;
use Tourze\TrainCategoryBundle\Repository\CategoryRepository;
use Tourze\TrainCategoryBundle\Service\CategoryIntegrationService;
use Tourze\TrainCategoryBundle\Service\CategoryService;
use Tourze\TrainCategoryBundle\Service\CategoryRequirementService;
use Tourze\TrainCategoryBundle\Service\CategoryValidationService;

class CategoryIntegrationServiceTest extends TestCase
{
    private CategoryRepository $categoryRepository;
    private CategoryService $categoryService;
    private CategoryRequirementService $requirementService;
    private CategoryValidationService $validationService;
    private CategoryIntegrationService $service;

    protected function setUp(): void
    {
        $this->categoryRepository = $this->createMock(CategoryRepository::class);
        $this->categoryService = $this->createMock(CategoryService::class);
        $this->requirementService = $this->createMock(CategoryRequirementService::class);
        $this->validationService = $this->createMock(CategoryValidationService::class);
        $this->service = new CategoryIntegrationService(
            $this->categoryRepository,
            $this->categoryService,
            $this->requirementService,
            $this->validationService
        );
    }

    public function testServiceExists(): void
    {
        $this->assertInstanceOf(CategoryIntegrationService::class, $this->service);
    }
}