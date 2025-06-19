<?php

namespace Tourze\TrainCategoryBundle\Tests\Unit\Entity;

use PHPUnit\Framework\TestCase;
use Tourze\TrainCategoryBundle\Entity\Category;
use Tourze\TrainCategoryBundle\Entity\CategoryRequirement;

class CategoryRequirementTest extends TestCase
{
    private CategoryRequirement $requirement;
    private Category $category;

    protected function setUp(): void
    {
        $this->category = new Category();
        $this->category->setTitle('测试分类');

        $this->requirement = new CategoryRequirement();
        $this->requirement->setCategory($this->category);
    }

    public function testGettersAndSetters(): void
    {
        // 测试分类关联
        $this->assertSame($this->category, $this->requirement->getCategory());

        // 测试学时设置
        $this->requirement->setInitialTrainingHours(72);
        $this->assertEquals(72, $this->requirement->getInitialTrainingHours());

        $this->requirement->setRefreshTrainingHours(24);
        $this->assertEquals(24, $this->requirement->getRefreshTrainingHours());

        $this->requirement->setTheoryHours(48);
        $this->assertEquals(48, $this->requirement->getTheoryHours());

        $this->requirement->setPracticeHours(24);
        $this->assertEquals(24, $this->requirement->getPracticeHours());

        // 测试证书有效期
        $this->requirement->setCertificateValidityPeriod(36);
        $this->assertEquals(36, $this->requirement->getCertificateValidityPeriod());

        // 测试布尔字段
        $this->requirement->setRequiresPracticalExam(true);
        $this->assertTrue($this->requirement->isRequiresPracticalExam());

        $this->requirement->setRequiresOnSiteTraining(true);
        $this->assertTrue($this->requirement->isRequiresOnSiteTraining());

        // 测试年龄要求
        $this->requirement->setMinimumAge(18);
        $this->assertEquals(18, $this->requirement->getMinimumAge());

        $this->requirement->setMaximumAge(60);
        $this->assertEquals(60, $this->requirement->getMaximumAge());

        // 测试数组字段
        $prerequisites = ['身体健康', '无色盲色弱'];
        $this->requirement->setPrerequisites($prerequisites);
        $this->assertEquals($prerequisites, $this->requirement->getPrerequisites());

        $educationRequirements = ['初中及以上学历'];
        $this->requirement->setEducationRequirements($educationRequirements);
        $this->assertEquals($educationRequirements, $this->requirement->getEducationRequirements());

        $healthRequirements = ['体检合格', '听力正常'];
        $this->requirement->setHealthRequirements($healthRequirements);
        $this->assertEquals($healthRequirements, $this->requirement->getHealthRequirements());

        $experienceRequirements = ['相关工作经验'];
        $this->requirement->setExperienceRequirements($experienceRequirements);
        $this->assertEquals($experienceRequirements, $this->requirement->getExperienceRequirements());

        // 测试备注
        $this->requirement->setRemarks('测试备注');
        $this->assertEquals('测试备注', $this->requirement->getRemarks());
    }

    public function testGetTotalHours(): void
    {
        $this->requirement->setTheoryHours(40);
        $this->requirement->setPracticeHours(20);

        $this->assertEquals(60, $this->requirement->getTotalHours());
    }

    public function testValidateHours(): void
    {
        // 测试正常配置
        $this->requirement->setInitialTrainingHours(72);
        $this->requirement->setRefreshTrainingHours(24);
        $this->requirement->setTheoryHours(48);
        $this->requirement->setPracticeHours(24);
        $this->requirement->setMinimumAge(18);
        $this->requirement->setMaximumAge(60);
        $this->requirement->setCertificateValidityPeriod(36);

        $errors = $this->requirement->validateHours();
        $this->assertEmpty($errors);

        // 测试负数学时
        $this->requirement->setInitialTrainingHours(-10);
        $errors = $this->requirement->validateHours();
        $this->assertContains('初训学时不能为负数', $errors);

        // 测试学时超出限制
        $this->requirement->setInitialTrainingHours(50);
        $this->requirement->setTheoryHours(40);
        $this->requirement->setPracticeHours(20); // 总计60 > 50
        $errors = $this->requirement->validateHours();
        $this->assertContains('理论学时和实操学时之和不能超过初训学时', $errors);

        // 测试年龄范围错误
        $this->requirement->setMinimumAge(70);
        $this->requirement->setMaximumAge(60);
        $errors = $this->requirement->validateHours();
        $this->assertContains('最高年龄限制应大于最低年龄且不超过70岁', $errors);
    }

    public function testCheckAgeRequirement(): void
    {
        $this->requirement->setMinimumAge(18);
        $this->requirement->setMaximumAge(60);

        $this->assertTrue($this->requirement->checkAgeRequirement(25));
        $this->assertTrue($this->requirement->checkAgeRequirement(18));
        $this->assertTrue($this->requirement->checkAgeRequirement(60));
        $this->assertFalse($this->requirement->checkAgeRequirement(17));
        $this->assertFalse($this->requirement->checkAgeRequirement(61));
    }

    public function testGetRequirementSummary(): void
    {
        $this->requirement->setInitialTrainingHours(72);
        $this->requirement->setRefreshTrainingHours(24);
        $this->requirement->setTheoryHours(48);
        $this->requirement->setPracticeHours(24);
        $this->requirement->setRequiresPracticalExam(true);
        $this->requirement->setRequiresOnSiteTraining(true);
        $this->requirement->setMinimumAge(18);
        $this->requirement->setMaximumAge(60);
        $this->requirement->setCertificateValidityPeriod(36);

        $summary = $this->requirement->getRequirementSummary();

        $this->assertStringContainsString('初训72学时', $summary);
        $this->assertStringContainsString('复训24学时', $summary);
        $this->assertStringContainsString('理论48+实操24学时', $summary);
        $this->assertStringContainsString('需实操考试', $summary);
        $this->assertStringContainsString('需现场培训', $summary);
        $this->assertStringContainsString('年龄18-60岁', $summary);
        $this->assertStringContainsString('证书有效期36个月', $summary);
    }

    public function testDefaultValues(): void
    {
        $requirement = new CategoryRequirement();

        $this->assertEquals(0, $requirement->getInitialTrainingHours());
        $this->assertEquals(0, $requirement->getRefreshTrainingHours());
        $this->assertEquals(0, $requirement->getTheoryHours());
        $this->assertEquals(0, $requirement->getPracticeHours());
        $this->assertEquals(36, $requirement->getCertificateValidityPeriod());
        $this->assertFalse($requirement->isRequiresPracticalExam());
        $this->assertFalse($requirement->isRequiresOnSiteTraining());
        $this->assertEquals(18, $requirement->getMinimumAge());
        $this->assertEquals(60, $requirement->getMaximumAge());
        $this->assertEquals([], $requirement->getPrerequisites());
        $this->assertEquals([], $requirement->getEducationRequirements());
        $this->assertEquals([], $requirement->getHealthRequirements());
        $this->assertEquals([], $requirement->getExperienceRequirements());
        $this->assertNull($requirement->getRemarks());
    }

    public function testTimeFields(): void
    {
        $now = new \DateTimeImmutable();
        
        $this->requirement->setCreateTime($now);
        $this->assertEquals($now, $this->requirement->getCreateTime());

        $this->requirement->setUpdateTime($now);
        $this->assertEquals($now, $this->requirement->getUpdateTime());
    }
} 