<?php

namespace Tourze\TrainCategoryBundle\Tests\Integration\Service;

use PHPUnit\Framework\TestCase;
use Tourze\TrainCategoryBundle\Service\CategoryValidationService;
use Tourze\TrainCategoryBundle\Service\CategoryService;
use Tourze\TrainCategoryBundle\Service\CategoryRequirementService;

class CategoryValidationServiceTest extends TestCase
{
    private CategoryService $categoryService;
    private CategoryRequirementService $requirementService;
    private CategoryValidationService $service;

    protected function setUp(): void
    {
        $this->categoryService = $this->createMock(CategoryService::class);
        $this->requirementService = $this->createMock(CategoryRequirementService::class);
        $this->service = new CategoryValidationService(
            $this->categoryService,
            $this->requirementService
        );
    }

    public function testServiceExists(): void
    {
        $this->assertInstanceOf(CategoryValidationService::class, $this->service);
    }
}