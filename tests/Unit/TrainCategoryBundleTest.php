<?php

namespace Tourze\TrainCategoryBundle\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Tourze\TrainCategoryBundle\TrainCategoryBundle;

class TrainCategoryBundleTest extends TestCase
{
    public function testBuild(): void
    {
        $bundle = new TrainCategoryBundle();
        $container = new ContainerBuilder();

        $bundle->build($container);

        $this->assertInstanceOf(TrainCategoryBundle::class, $bundle);
    }
}