<?php

namespace Tourze\TrainCategoryBundle\Tests\Integration\Service;

use PHPUnit\Framework\TestCase;
use Doctrine\ORM\EntityManagerInterface;
use Tourze\TrainCategoryBundle\Repository\CategoryRepository;
use Tourze\TrainCategoryBundle\Service\CategoryImportExportService;
use Tourze\TrainCategoryBundle\Service\CategoryService;
use Tourze\TrainCategoryBundle\Service\CategoryRequirementService;

class CategoryImportExportServiceTest extends TestCase
{
    private CategoryRepository $categoryRepository;
    private CategoryService $categoryService;
    private CategoryRequirementService $requirementService;
    private EntityManagerInterface $entityManager;
    private CategoryImportExportService $service;

    protected function setUp(): void
    {
        $this->categoryRepository = $this->createMock(CategoryRepository::class);
        $this->categoryService = $this->createMock(CategoryService::class);
        $this->requirementService = $this->createMock(CategoryRequirementService::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->service = new CategoryImportExportService(
            $this->categoryRepository,
            $this->categoryService,
            $this->requirementService,
            $this->entityManager
        );
    }

    public function testServiceExists(): void
    {
        $this->assertInstanceOf(CategoryImportExportService::class, $this->service);
    }
}