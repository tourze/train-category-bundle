<?php

namespace Tourze\TrainCategoryBundle\Tests\Unit\Controller\Admin;

use PHPUnit\Framework\TestCase;
use Tourze\TrainCategoryBundle\Controller\Admin\TrainCategoryCrudController;
use Tourze\TrainCategoryBundle\Entity\Category;

class TrainCategoryCrudControllerTest extends TestCase
{
    private TrainCategoryCrudController $controller;

    protected function setUp(): void
    {
        $this->controller = new TrainCategoryCrudController();
    }

    public function test_getEntityFqcn_returnsCorrectEntityClass(): void
    {
        $this->assertEquals(Category::class, TrainCategoryCrudController::getEntityFqcn());
    }

    public function test_controllerExists(): void
    {
        $this->assertInstanceOf(TrainCategoryCrudController::class, $this->controller);
    }

    public function test_controllerExtendsAbstractCrudController(): void
    {
        $this->assertInstanceOf(
            \EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController::class,
            $this->controller
        );
    }
} 