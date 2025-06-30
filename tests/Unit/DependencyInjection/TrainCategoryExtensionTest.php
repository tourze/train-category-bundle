<?php

namespace Tourze\TrainCategoryBundle\Tests\Unit\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Tourze\TrainCategoryBundle\DependencyInjection\TrainCategoryExtension;

class TrainCategoryExtensionTest extends TestCase
{
    public function testLoad(): void
    {
        $extension = new TrainCategoryExtension();
        $container = new ContainerBuilder();

        $extension->load([], $container);

        $this->assertInstanceOf(TrainCategoryExtension::class, $extension);
    }
}