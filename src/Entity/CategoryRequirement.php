<?php

namespace Tourze\TrainCategoryBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Stringable;
use Tourze\DoctrineSnowflakeBundle\Traits\SnowflakeKeyAware;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\TrainCategoryBundle\Repository\CategoryRequirementRepository;

/**
 * 分类培训要求实体
 *
 * 定义每个培训分类的具体要求，包括学时、考试要求、前置条件等
 */
#[ORM\Entity(repositoryClass: CategoryRequirementRepository::class)]
#[ORM\Table(name: 'train_category_requirement', options: ['comment' => '分类培训要求'])]
#[ORM\Index(columns: ['category_id'], name: 'idx_category_requirement_category')]
class CategoryRequirement implements Stringable
{
    use TimestampableAware;
    use SnowflakeKeyAware;

    #[ORM\OneToOne(targetEntity: Category::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Category $category;

    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '初训学时', 'default' => 0])]
    private int $initialTrainingHours = 0;

    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '复训学时', 'default' => 0])]
    private int $refreshTrainingHours = 0;

    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '理论学时', 'default' => 0])]
    private int $theoryHours = 0;

    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '实操学时', 'default' => 0])]
    private int $practiceHours = 0;

    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '证书有效期（月）', 'default' => 36])]
    private int $certificateValidityPeriod = 36;

    #[ORM\Column(type: Types::BOOLEAN, options: ['comment' => '是否需要实操考试', 'default' => false])]
    private bool $requiresPracticalExam = false;

    #[ORM\Column(type: Types::BOOLEAN, options: ['comment' => '是否需要现场培训', 'default' => false])]
    private bool $requiresOnSiteTraining = false;

    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '最低年龄要求', 'default' => 18])]
    private int $minimumAge = 18;

    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '最高年龄限制', 'default' => 60])]
    private int $maximumAge = 60;

    /**
     * @var array<int, string>
     */
    #[ORM\Column(type: Types::JSON, options: ['comment' => '前置条件'])]
    private array $prerequisites = [];

    /**
     * @var array<int, string>
     */
    #[ORM\Column(type: Types::JSON, options: ['comment' => '学历要求'])]
    private array $educationRequirements = [];

    /**
     * @var array<int, string>
     */
    #[ORM\Column(type: Types::JSON, options: ['comment' => '健康要求'])]
    private array $healthRequirements = [];

    /**
     * @var array<int, string>
     */
    #[ORM\Column(type: Types::JSON, options: ['comment' => '工作经验要求'])]
    private array $experienceRequirements = [];

    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '备注说明'])]
    private ?string $remarks = null;


    public function getCategory(): Category
    {
        return $this->category;
    }

    public function setCategory(Category $category): self
    {
        $this->category = $category;
        return $this;
    }

    public function getInitialTrainingHours(): int
    {
        return $this->initialTrainingHours;
    }

    public function setInitialTrainingHours(int $initialTrainingHours): self
    {
        $this->initialTrainingHours = $initialTrainingHours;
        return $this;
    }

    public function getRefreshTrainingHours(): int
    {
        return $this->refreshTrainingHours;
    }

    public function setRefreshTrainingHours(int $refreshTrainingHours): self
    {
        $this->refreshTrainingHours = $refreshTrainingHours;
        return $this;
    }

    public function getTheoryHours(): int
    {
        return $this->theoryHours;
    }

    public function setTheoryHours(int $theoryHours): self
    {
        $this->theoryHours = $theoryHours;
        return $this;
    }

    public function getPracticeHours(): int
    {
        return $this->practiceHours;
    }

    public function setPracticeHours(int $practiceHours): self
    {
        $this->practiceHours = $practiceHours;
        return $this;
    }

    public function getCertificateValidityPeriod(): int
    {
        return $this->certificateValidityPeriod;
    }

    public function setCertificateValidityPeriod(int $certificateValidityPeriod): self
    {
        $this->certificateValidityPeriod = $certificateValidityPeriod;
        return $this;
    }

    public function isRequiresPracticalExam(): bool
    {
        return $this->requiresPracticalExam;
    }

    public function setRequiresPracticalExam(bool $requiresPracticalExam): self
    {
        $this->requiresPracticalExam = $requiresPracticalExam;
        return $this;
    }

    public function isRequiresOnSiteTraining(): bool
    {
        return $this->requiresOnSiteTraining;
    }

    public function setRequiresOnSiteTraining(bool $requiresOnSiteTraining): self
    {
        $this->requiresOnSiteTraining = $requiresOnSiteTraining;
        return $this;
    }

    public function getMinimumAge(): int
    {
        return $this->minimumAge;
    }

    public function setMinimumAge(int $minimumAge): self
    {
        $this->minimumAge = $minimumAge;
        return $this;
    }

    public function getMaximumAge(): int
    {
        return $this->maximumAge;
    }

    public function setMaximumAge(int $maximumAge): self
    {
        $this->maximumAge = $maximumAge;
        return $this;
    }

    /**
     * @return array<int, string>
     */
    public function getPrerequisites(): array
    {
        return $this->prerequisites;
    }

    /**
     * @param array<int, string> $prerequisites
     */
    public function setPrerequisites(array $prerequisites): self
    {
        $this->prerequisites = $prerequisites;
        return $this;
    }

    /**
     * @return array<int, string>
     */
    public function getEducationRequirements(): array
    {
        return $this->educationRequirements;
    }

    /**
     * @param array<int, string> $educationRequirements
     */
    public function setEducationRequirements(array $educationRequirements): self
    {
        $this->educationRequirements = $educationRequirements;
        return $this;
    }

    /**
     * @return array<int, string>
     */
    public function getHealthRequirements(): array
    {
        return $this->healthRequirements;
    }

    /**
     * @param array<int, string> $healthRequirements
     */
    public function setHealthRequirements(array $healthRequirements): self
    {
        $this->healthRequirements = $healthRequirements;
        return $this;
    }

    /**
     * @return array<int, string>
     */
    public function getExperienceRequirements(): array
    {
        return $this->experienceRequirements;
    }

    /**
     * @param array<int, string> $experienceRequirements
     */
    public function setExperienceRequirements(array $experienceRequirements): self
    {
        $this->experienceRequirements = $experienceRequirements;
        return $this;
    }

    public function getRemarks(): ?string
    {
        return $this->remarks;
    }

    public function setRemarks(?string $remarks): self
    {
        $this->remarks = $remarks;
        return $this;
    }

    /**
     * 获取总学时（理论+实操）
     */
    public function getTotalHours(): int
    {
        return $this->theoryHours + $this->practiceHours;
    }

    /**
     * 验证学时配置是否合理
     * @return array<int, string>
     */
    public function validateHours(): array
    {
        $errors = [];

        if ($this->initialTrainingHours < 0) {
            $errors[] = '初训学时不能为负数';
        }

        if ($this->refreshTrainingHours < 0) {
            $errors[] = '复训学时不能为负数';
        }

        if ($this->theoryHours < 0) {
            $errors[] = '理论学时不能为负数';
        }

        if ($this->practiceHours < 0) {
            $errors[] = '实操学时不能为负数';
        }

        $totalHours = $this->getTotalHours();
        if ($this->initialTrainingHours > 0 && $totalHours > $this->initialTrainingHours) {
            $errors[] = '理论学时和实操学时之和不能超过初训学时';
        }

        if ($this->minimumAge < 16 || $this->minimumAge > 65) {
            $errors[] = '最低年龄要求应在16-65岁之间';
        }

        if ($this->maximumAge < $this->minimumAge || $this->maximumAge > 70) {
            $errors[] = '最高年龄限制应大于最低年龄且不超过70岁';
        }

        if ($this->certificateValidityPeriod < 1 || $this->certificateValidityPeriod > 120) {
            $errors[] = '证书有效期应在1-120个月之间';
        }

        return $errors;
    }

    /**
     * 检查是否满足年龄要求
     */
    public function checkAgeRequirement(int $age): bool
    {
        return $age >= $this->minimumAge && $age <= $this->maximumAge;
    }

    /**
     * 获取要求摘要
     */
    public function getRequirementSummary(): string
    {
        $summary = [];

        if ($this->initialTrainingHours > 0) {
            $summary[] = "初训{$this->initialTrainingHours}学时";
        }

        if ($this->refreshTrainingHours > 0) {
            $summary[] = "复训{$this->refreshTrainingHours}学时";
        }

        if ($this->getTotalHours() > 0) {
            $summary[] = "理论{$this->theoryHours}+实操{$this->practiceHours}学时";
        }

        if ($this->requiresPracticalExam) {
            $summary[] = "需实操考试";
        }

        if ($this->requiresOnSiteTraining) {
            $summary[] = "需现场培训";
        }

        $summary[] = "年龄{$this->minimumAge}-{$this->maximumAge}岁";
        $summary[] = "证书有效期{$this->certificateValidityPeriod}个月";

        return implode('，', $summary);
    }

    public function __toString(): string
    {
        return (string) $this->id;
    }
} 