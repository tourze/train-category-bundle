<?php

namespace Tourze\TrainCategoryBundle\Tests\Integration\Service;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Tourze\TrainCategoryBundle\Entity\Category;
use Tourze\TrainCategoryBundle\Entity\CategoryRequirement;
use Tourze\TrainCategoryBundle\Exception\CategoryRequirementValidationException;
use Tourze\TrainCategoryBundle\Repository\CategoryRequirementRepository;
use Tourze\TrainCategoryBundle\Service\CategoryRequirementService;

class CategoryRequirementServiceTest extends TestCase
{
    private CategoryRequirementRepository $requirementRepository;
    private EntityManagerInterface $entityManager;
    private CategoryRequirementService $service;

    protected function setUp(): void
    {
        $this->requirementRepository = $this->createMock(CategoryRequirementRepository::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->service = new CategoryRequirementService(
            $this->requirementRepository,
            $this->entityManager
        );
    }

    public function testSetCategoryRequirement(): void
    {
        $category = new Category();
        $requirement = new CategoryRequirement();
        $requirement->setCategory($category);
        
        $this->requirementRepository
            ->expects($this->once())
            ->method('findByCategory')
            ->with($category)
            ->willReturn(null);

        $this->entityManager
            ->expects($this->once())
            ->method('persist');

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        $requirements = [
            'initialTrainingHours' => 48,
            'refreshTrainingHours' => 16,
        ];

        $result = $this->service->setCategoryRequirement($category, $requirements);

        $this->assertInstanceOf(CategoryRequirement::class, $result);
        $this->assertSame($category, $result->getCategory());
    }

    public function testSetCategoryRequirementWithValidationError(): void
    {
        $category = new Category();
        $requirement = $this->createMock(CategoryRequirement::class);
        
        $this->requirementRepository
            ->expects($this->once())
            ->method('findByCategory')
            ->with($category)
            ->willReturn($requirement);

        $requirement
            ->expects($this->once())
            ->method('validateHours')
            ->willReturn(['错误信息']);

        $this->expectException(CategoryRequirementValidationException::class);

        $this->service->setCategoryRequirement($category, []);
    }

    public function testGetCategoryRequirement(): void
    {
        $category = new Category();
        $requirement = new CategoryRequirement();
        
        $this->requirementRepository
            ->expects($this->once())
            ->method('findByCategory')
            ->with($category)
            ->willReturn($requirement);

        $result = $this->service->getCategoryRequirement($category);

        $this->assertSame($requirement, $result);
    }

    public function testValidateTrainingHours(): void
    {
        $category = new Category();
        $requirement = $this->createMock(CategoryRequirement::class);
        
        $this->requirementRepository
            ->expects($this->once())
            ->method('findByCategory')
            ->with($category)
            ->willReturn($requirement);

        $requirement
            ->expects($this->once())
            ->method('getInitialTrainingHours')
            ->willReturn(48);

        $result = $this->service->validateTrainingHours($category, 50, 'initial');

        $this->assertTrue($result);
    }

    public function testCalculateTotalHours(): void
    {
        $category = new Category();
        $requirement = $this->createMock(CategoryRequirement::class);
        
        $this->requirementRepository
            ->expects($this->once())
            ->method('findByCategory')
            ->with($category)
            ->willReturn($requirement);

        $requirement
            ->expects($this->once())
            ->method('getTotalHours')
            ->willReturn(72);

        $result = $this->service->calculateTotalHours($category);

        $this->assertEquals(72, $result);
    }
}