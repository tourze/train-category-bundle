<?php

namespace Tourze\TrainCategoryBundle\Tests\Integration\Controller\Admin;

use PHPUnit\Framework\TestCase;
use Tourze\TrainCategoryBundle\Controller\Admin\CategoryRequirementCrudController;

class CategoryRequirementCrudControllerTest extends TestCase
{
    public function testControllerExists(): void
    {
        $controller = new CategoryRequirementCrudController();
        $this->assertInstanceOf(CategoryRequirementCrudController::class, $controller);
    }
}