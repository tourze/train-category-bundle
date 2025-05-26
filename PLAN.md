# train-category-bundle 开发计划

## 1. 功能描述

培训分类管理包，负责安全生产培训资源的分类管理功能。支持树形分类结构，管理培训类别（特种作业、主要负责人、安全管理人员）、行业分类（矿山、危化品、石油天然气等）、作业类别（电工、焊接、高处作业等）和培训大纲管理。

## 2. 完整能力要求

### 2.1 现有能力（已实现）

- ✅ **树形分类结构管理**（父子级关系）
  - 实现了Category实体的自关联
  - 支持无限层级的树形结构
  - 提供了addChild/removeChild方法

- ✅ **分类基本信息管理**（名称、排序）
  - title字段存储分类名称（最大100字符）
  - sortNumber字段支持排序（数值越大排序越靠前）
  - 实现了__toString方法显示完整路径

- ✅ **EasyAdmin管理界面**
  - 实现了TrainCategoryCrudController
  - 支持CRUD操作（创建、读取、更新、删除）
  - 提供搜索和过滤功能
  - 支持树形结构显示和编辑
  - 配置了中文界面标签

- ✅ **分类排序功能**
  - sortNumber字段支持排序
  - 默认按sortNumber DESC, id DESC排序
  - 在管理界面中可编辑排序值

- ✅ **时间戳和用户追踪**
  - 使用doctrine-timestamp-bundle实现createTime/updateTime
  - 使用doctrine-user-bundle实现createdBy/updatedBy
  - 支持索引优化

- ✅ **API接口支持**
  - 实现了GetJobTrainingCategory JSON-RPC接口
  - 支持按父级分类查询子分类
  - 提供了ApiArrayInterface和AdminArrayInterface

- ✅ **基础架构完整**
  - Bundle结构完整（Entity、Repository、Controller、DI配置）
  - 使用雪花算法生成ID
  - 支持依赖注入和服务配置
  - 包含PHPStan和PHPUnit配置

- ✅ **AdminMenu配置已实现**
  - 创建了AdminMenu服务类
  - 集成到EasyAdmin菜单系统
  - 包含分类管理和培训要求两个子菜单
  - 已通过单元测试验证

### 2.2 现有能力的不足

- ❌ **缺少CategoryService服务层**
  - 目前只有Repository，缺少业务逻辑封装
  - 需要实现分类层次结构的业务方法

- ❌ **缺少单元测试**
  - 已有部分单元测试（52个测试用例，134个断言）
  - 需要补充更多实体和服务的测试

### 2.3 需要增强的能力

#### 2.3.1 符合AQ8011-2023的分类标准

- [ ] **培训类别标准化管理**
  - 特种作业人员培训
  - 生产经营单位主要负责人培训
  - 安全生产管理人员培训
  - 其他从业人员培训

- [ ] **行业分类标准化管理**
  - 矿山行业
  - 危险化学品行业
  - 石油天然气开采行业
  - 金属冶炼行业
  - 建筑施工行业
  - 道路运输行业
  - 其他行业

- [ ] **特种作业类别管理**
  - 电工作业
  - 焊接与热切割作业
  - 高处作业
  - 制冷与空调作业
  - 煤矿安全作业
  - 金属非金属矿山安全作业
  - 石油天然气安全作业
  - 冶金（有色）生产安全作业
  - 危险化学品安全作业
  - 烟花爆竹安全作业

#### 2.3.2 培训学时要求配置

- [ ] 不同分类的学时要求配置
- [ ] 理论学时和实操学时分别管理
- [ ] 复训学时要求配置
- [ ] 学时要求的版本管理

#### 2.3.3 分类与教师资质关联

- [ ] 教师可授课分类管理
- [ ] 教师资质等级与分类的匹配
- [ ] 教师授课范围限制

#### 2.3.4 培训大纲管理

- [ ] 分类对应的培训大纲
- [ ] 大纲版本控制
- [ ] 大纲审核流程
- [ ] 大纲更新通知

#### 2.3.5 证书类型关联

- [ ] 分类对应的证书类型
- [ ] 证书有效期配置
- [ ] 证书样式模板关联

## 3. 现有实体设计分析

### 3.1 现有实体

#### Category（分类主表）

**表名**: `job_training_category`

**字段分析**:

- `id`: BIGINT，雪花算法生成，主键
- `title`: VARCHAR(100)，分类名称，必填
- `parent_id`: BIGINT，父级分类ID，可空，外键关联自身
- `sort_number`: INTEGER，排序值，默认0，有索引
- `created_by`: VARCHAR，创建人，可空
- `updated_by`: VARCHAR，更新人，可空
- `create_time`: DATETIME，创建时间，有索引
- `update_time`: DATETIME，更新时间

**关联关系**:

- 自关联：parent/children（树形结构）

**特性**:

- 实现了Stringable接口，支持路径显示
- 实现了ApiArrayInterface和AdminArrayInterface
- 支持Symfony序列化分组
- 使用了多个Doctrine Bundle的特性

**注释**: "一级是人员类型，二级是行业类别"

### 3.2 需要新增的实体

#### TrainingOutline（培训大纲）

```php
#[ORM\Entity]
#[ORM\Table(name: 'train_category_training_outline', options: ['comment' => '培训大纲'])]
class TrainingOutline
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(SnowflakeIdGenerator::class)]
    #[ORM\Column(type: Types::BIGINT)]
    private string $id;

    #[ORM\ManyToOne(targetEntity: Category::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Category $category;

    #[ORM\Column(length: 200, options: ['comment' => '大纲标题'])]
    private string $outlineTitle;

    #[ORM\Column(type: Types::TEXT, options: ['comment' => '大纲内容'])]
    private string $outlineContent;

    #[ORM\Column(length: 50, options: ['comment' => '版本号'])]
    private string $version;

    #[ORM\Column(length: 20, options: ['comment' => '状态：draft,reviewing,published,deprecated'])]
    private string $status;

    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '理论学时'])]
    private int $theoryHours;

    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '实操学时'])]
    private int $practiceHours;

    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '总学时'])]
    private int $totalHours;

    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '适用范围'])]
    private ?string $applicableScope = null;

    #[ORM\Column(type: Types::JSON, options: ['comment' => '学习目标'])]
    private array $learningObjectives = [];

    #[ORM\Column(type: Types::JSON, options: ['comment' => '知识点'])]
    private array $knowledgePoints = [];

    #[ORM\Column(type: Types::DATE_MUTABLE, options: ['comment' => '生效日期'])]
    private \DateTimeInterface $effectiveDate;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true, options: ['comment' => '失效日期'])]
    private ?\DateTimeInterface $expiryDate = null;

    #[CreateTimeColumn]
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private \DateTimeInterface $createTime;

    #[UpdateTimeColumn]
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private \DateTimeInterface $updateTime;
}
```

#### CategoryRequirement（分类要求）

```php
#[ORM\Entity]
#[ORM\Table(name: 'train_category_requirement', options: ['comment' => '分类培训要求'])]
class CategoryRequirement
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(SnowflakeIdGenerator::class)]
    #[ORM\Column(type: Types::BIGINT)]
    private string $id;

    #[ORM\OneToOne(targetEntity: Category::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Category $category;

    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '初训学时'])]
    private int $initialTrainingHours;

    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '复训学时'])]
    private int $refreshTrainingHours;

    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '理论学时'])]
    private int $theoryHours;

    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '实操学时'])]
    private int $practiceHours;

    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '证书有效期（月）'])]
    private int $certificateValidityPeriod;

    #[ORM\Column(type: Types::BOOLEAN, options: ['comment' => '是否需要实操考试'])]
    private bool $requiresPracticalExam = false;

    #[ORM\Column(type: Types::BOOLEAN, options: ['comment' => '是否需要现场培训'])]
    private bool $requiresOnSiteTraining = false;

    #[ORM\Column(type: Types::JSON, options: ['comment' => '前置条件'])]
    private array $prerequisites = [];

    #[CreateTimeColumn]
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private \DateTimeInterface $createTime;

    #[UpdateTimeColumn]
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private \DateTimeInterface $updateTime;
}
```

#### CategoryCertificateType（分类证书类型）

```php
#[ORM\Entity]
#[ORM\Table(name: 'train_category_certificate_type', options: ['comment' => '分类证书类型'])]
class CategoryCertificateType
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(SnowflakeIdGenerator::class)]
    #[ORM\Column(type: Types::BIGINT)]
    private string $id;

    #[ORM\ManyToOne(targetEntity: Category::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Category $category;

    #[ORM\Column(length: 50, options: ['comment' => '证书类型'])]
    private string $certificateType;

    #[ORM\Column(length: 100, options: ['comment' => '证书名称'])]
    private string $certificateName;

    #[ORM\Column(length: 100, options: ['comment' => '证书编码规则'])]
    private string $certificateCode;

    #[ORM\Column(length: 255, nullable: true, options: ['comment' => '证书模板路径'])]
    private ?string $templatePath = null;

    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '有效期（月）'])]
    private int $validityPeriod;

    #[ORM\Column(type: Types::BOOLEAN, options: ['comment' => '是否默认证书'])]
    private bool $isDefault = false;

    #[CreateTimeColumn]
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private \DateTimeInterface $createTime;

    #[UpdateTimeColumn]
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private \DateTimeInterface $updateTime;
}
```

#### CategoryTeacherQualification（分类教师资质）

```php
#[ORM\Entity]
#[ORM\Table(name: 'train_category_teacher_qualification', options: ['comment' => '分类教师资质要求'])]
class CategoryTeacherQualification
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(SnowflakeIdGenerator::class)]
    #[ORM\Column(type: Types::BIGINT)]
    private string $id;

    #[ORM\ManyToOne(targetEntity: Category::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Category $category;

    #[ORM\Column(length: 50, options: ['comment' => '资质等级'])]
    private string $qualificationLevel;

    #[ORM\Column(length: 100, options: ['comment' => '资质名称'])]
    private string $qualificationName;

    #[ORM\Column(type: Types::JSON, options: ['comment' => '要求的证书'])]
    private array $requiredCertificates = [];

    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '最低工作经验（年）'])]
    private int $minWorkExperience;

    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '最低教学经验（年）'])]
    private int $minTeachingExperience;

    #[ORM\Column(type: Types::JSON, options: ['comment' => '学历要求'])]
    private array $requiredEducation = [];

    #[ORM\Column(type: Types::BOOLEAN, options: ['comment' => '是否需要培训师证书'])]
    private bool $requiresTrainingCertificate = false;

    #[CreateTimeColumn]
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private \DateTimeInterface $createTime;

    #[UpdateTimeColumn]
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private \DateTimeInterface $updateTime;
}
```

#### CategoryStandard（分类标准）

```php
#[ORM\Entity]
#[ORM\Table(name: 'train_category_standard', options: ['comment' => '分类标准'])]
class CategoryStandard
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(SnowflakeIdGenerator::class)]
    #[ORM\Column(type: Types::BIGINT)]
    private string $id;

    #[ORM\ManyToOne(targetEntity: Category::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Category $category;

    #[ORM\Column(length: 50, options: ['comment' => '标准编码'])]
    private string $standardCode;

    #[ORM\Column(length: 200, options: ['comment' => '标准名称'])]
    private string $standardName;

    #[ORM\Column(length: 50, options: ['comment' => '标准版本'])]
    private string $standardVersion;

    #[ORM\Column(length: 100, options: ['comment' => '发布机构'])]
    private string $issuingAuthority;

    #[ORM\Column(type: Types::DATE_MUTABLE, options: ['comment' => '发布日期'])]
    private \DateTimeInterface $issueDate;

    #[ORM\Column(type: Types::DATE_MUTABLE, options: ['comment' => '生效日期'])]
    private \DateTimeInterface $effectiveDate;

    #[ORM\Column(type: Types::TEXT, options: ['comment' => '标准内容'])]
    private string $standardContent;

    #[ORM\Column(length: 20, options: ['comment' => '状态：active,deprecated'])]
    private string $status = 'active';

    #[CreateTimeColumn]
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private \DateTimeInterface $createTime;

    #[UpdateTimeColumn]
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private \DateTimeInterface $updateTime;
}
```

## 4. 服务设计

### 4.1 需要新增的基础服务

#### CategoryService（基础分类服务）

```php
namespace Tourze\TrainCategoryBundle\Service;

class CategoryService
{
    public function __construct(
        private readonly CategoryRepository $categoryRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    // 基础CRUD方法
    public function createCategory(string $title, ?Category $parent = null, int $sortNumber = 0): Category;
    public function updateCategory(Category $category, array $data): Category;
    public function deleteCategory(Category $category): void;
    
    // 树形结构方法
    public function getCategoryTree(?Category $root = null): array;
    public function getCategoryPath(Category $category): array;
    public function moveCategoryTo(Category $category, ?Category $newParent): void;
    
    // 查询方法
    public function findByLevel(int $level): array;
    public function findRootCategories(): array;
    public function findLeafCategories(): array;
    
    // 标准化方法
    public function getStandardizedCategories(): array;
    public function getCategoryByType(string $type): array;
    public function validateCategoryStructure(Category $category): array;
    public function importStandardCategories(array $categories): void;
}
```

### 4.2 扩展服务

#### TrainingOutlineService

```php
class TrainingOutlineService
{
    public function createOutline(string $categoryId, array $outlineData): TrainingOutline;
    public function updateOutline(string $outlineId, array $outlineData): TrainingOutline;
    public function publishOutline(string $outlineId): TrainingOutline;
    public function getActiveOutline(string $categoryId): ?TrainingOutline;
    public function getOutlineHistory(string $categoryId): array;
    public function compareOutlineVersions(string $outlineId1, string $outlineId2): array;
}
```

#### CategoryRequirementService

```php
class CategoryRequirementService
{
    public function setCategoryRequirement(string $categoryId, array $requirements): CategoryRequirement;
    public function getCategoryRequirement(string $categoryId): ?CategoryRequirement;
    public function validateTrainingHours(string $categoryId, int $hours): bool;
    public function calculateTotalHours(string $categoryId): int;
    public function getRequirementsByType(string $type): array;
}
```

#### CategoryStandardService

```php
class CategoryStandardService
{
    public function importStandard(string $categoryId, array $standardData): CategoryStandard;
    public function updateStandard(string $standardId, array $standardData): CategoryStandard;
    public function getActiveStandards(string $categoryId): array;
    public function checkStandardCompliance(string $categoryId): bool;
    public function getStandardUpdates(): array;
}
```

#### CategoryValidationService

```php
class CategoryValidationService
{
    public function validateCategoryStructure(Category $category): array;
    public function validateTeacherQualification(string $categoryId, string $teacherId): bool;
    public function validateTrainingRequirements(string $categoryId, array $trainingData): array;
    public function checkCertificateEligibility(string $categoryId, string $userId): bool;
}
```

## 5. 立即需要修复的问题

### 5.1 代码Bug修复

- ✅ **修复TrainCategoryCrudController控制器冲突**
  - 将CategoryCrudController重命名为TrainCategoryCrudController
  - 解决了与coupon-core-bundle的控制器名称冲突
  - 更新了相关文档和测试

### 5.2 缺失功能补充

- [ ] **创建CategoryService服务类**
- ✅ **实现AdminMenu配置**
- [ ] **补充单元测试**

### 5.3 代码质量改进

- [ ] **完善Repository方法**
- [ ] **优化实体关联配置**
- [ ] **添加更多业务验证**

## 6. 依赖包

**已使用的依赖包**:

- `doctrine-indexed-bundle` - 索引管理 ✅
- `doctrine-timestamp-bundle` - 时间戳管理 ✅
- `doctrine-user-bundle` - 用户追踪 ✅
- `doctrine-snowflake-bundle` - ID生成 ✅
- `arrayable` - 数组转换接口 ✅

**需要考虑的额外依赖**:

- `doctrine-entity-checker-bundle` - 实体检查
- `easy-admin-extra-bundle` - EasyAdmin扩展功能

## 7. 测试计划

### 7.1 单元测试（急需补充）

- [ ] **Category实体测试**
  - 树形结构操作测试
  - 关联关系测试
  - 字符串转换测试

- [ ] **CategoryRepository测试**
  - getDefaultCategory方法测试
  - 基础查询方法测试

- [ ] **CategoryService测试**（待实现）
  - 业务逻辑测试
  - 树形操作测试

### 7.2 集成测试

- [ ] **EasyAdmin界面测试**
- [ ] **JSON-RPC接口测试**
- [ ] **数据库迁移测试**

### 7.3 功能测试

- [ ] **分类树形结构测试**
- [ ] **排序功能测试**
- [ ] **题库关联测试**

## 8. 部署和运维

### 8.1 部署要求

- **PHP**: 8.1+ (当前配置)
- **数据库**: MySQL 8.0+ / PostgreSQL 14+
- **缓存**: Redis（可选，用于性能优化）

### 8.2 数据库表结构

**现有表**:

- `job_training_category` - 分类主表

**计划新增表**:

- `train_category_training_outline` - 培训大纲
- `train_category_requirement` - 分类要求
- `train_category_certificate_type` - 证书类型
- `train_category_teacher_qualification` - 教师资质
- `train_category_standard` - 分类标准

### 8.3 监控指标

- 分类数据完整性
- API响应时间
- 管理界面使用情况
- 数据库查询性能

### 8.4 数据迁移

- [ ] **现有分类数据验证**
- [ ] **AQ8011-2023标准数据导入**
- [ ] **历史数据清理和优化**

## 9. 开发优先级

### 第一阶段：修复现有问题（1-2天）

1. ✅ 修复TrainCategoryCrudController的控制器冲突
2. 创建CategoryService基础服务
3. ✅ 实现AdminMenu配置
4. 补充基础单元测试

### 第二阶段：标准化改造（1周）

1. 实现AQ8011-2023标准分类
2. 创建CategoryRequirement实体和服务
3. 完善分类验证逻辑

### 第三阶段：功能扩展（2-3周）

1. 实现培训大纲管理
2. 添加证书类型关联
3. 实现教师资质管理
4. 完善标准管理功能

### 第四阶段：优化完善（1周）

1. 性能优化
2. 完善测试覆盖
3. 文档完善
4. 部署优化

---

**文档版本**: v1.1
**更新日期**: 2024年12月
**负责人**: 开发团队
**审核状态**: 待审核
