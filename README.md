# Train Category Bundle

培训分类管理包，用于管理安全生产培训资源的分类体系。

## 功能特性

- 🏗️ **树形分类结构**：支持无限层级的分类管理
- 📋 **AQ8011-2023标准**：符合国家安全生产培训标准
- ⚙️ **培训要求配置**：学时、证书、考试、年龄等要求管理
- 🔍 **高级搜索**：多条件搜索、智能推荐、相关分类
- 📊 **统计分析**：分类统计、使用分析、健康度报告
- 📤 **导入导出**：Excel/CSV格式的数据导入导出
- 🔗 **模块集成**：与其他培训模块的无缝集成
- 🎯 **EasyAdmin集成**：完整的后台管理界面

## 安装

```bash
composer require tourze/train-category-bundle
```

## 配置

### 1. 注册Bundle

```php
// config/bundles.php
return [
    // ...
    Tourze\TrainCategoryBundle\TrainCategoryBundle::class => ['all' => true],
];
```

### 2. 数据库迁移

```bash
php bin/console doctrine:migrations:diff
php bin/console doctrine:migrations:migrate
```

### 3. 加载数据填充

```bash
# 加载标准分类数据
php bin/console doctrine:fixtures:load --group=production

# 加载开发测试数据（包含测试数据）
php bin/console doctrine:fixtures:load --group=dev

# 仅加载基础分类数据
php bin/console doctrine:fixtures:load --append --fixtures=src/DataFixtures/CategoryFixtures.php

# 仅加载培训要求数据
php bin/console doctrine:fixtures:load --append --fixtures=src/DataFixtures/CategoryRequirementFixtures.php
```

## 数据填充说明

本包提供了完整的数据填充类，用于快速初始化分类数据：

### CategoryFixtures
基础分类数据填充，包含：
- **培训类别**：特种作业、主要负责人、安全管理人员、其他从业人员
- **行业分类**：矿山、危化品、石油天然气、金属冶炼、建筑施工、道路运输等
- **特种作业类别**：电工、焊接、高处作业、制冷空调、煤矿安全、危化品安全等

### CategoryRequirementFixtures
培训要求数据填充，为主要分类创建符合AQ8011-2023标准的培训要求：
- 学时要求（初训、复训、理论、实操）
- 证书有效期配置
- 考试要求（理论、实操）
- 年龄限制和前置条件

### CategoryDetailedFixtures
详细分类数据填充，为特种作业创建三级分类：
- 电工作业：低压电工、高压电工、防爆电气等
- 焊接作业：熔化焊接、压力焊、钎焊等
- 高处作业：登高架设、高处安装维护拆除等
- 其他特种作业的详细分类

### CategoryTestDataFixtures
测试数据填充（仅开发环境），包含：
- 边界情况测试数据
- 性能测试数据（100个子分类、10层深度嵌套）
- 功能验证数据
- 异常情况模拟数据

### 数据填充分组

```bash
# 生产环境数据（推荐）
php bin/console doctrine:fixtures:load --group=production

# 开发环境数据（包含测试数据）
php bin/console doctrine:fixtures:load --group=dev

# 自定义加载
php bin/console doctrine:fixtures:load --append \
  --fixtures=src/DataFixtures/CategoryFixtures.php \
  --fixtures=src/DataFixtures/CategoryRequirementFixtures.php
```

## 使用示例

### 基础分类操作

```php
use Tourze\TrainCategoryBundle\Service\CategoryService;

// 获取服务
$categoryService = $this->container->get(CategoryService::class);

// 创建分类
$category = $categoryService->createCategory('新分类', $parentCategory);

// 获取树形结构
$tree = $categoryService->getCategoryTree();

// 获取子分类
$children = $categoryService->getChildren($parentCategory);
```

### 高级搜索

```php
use Tourze\TrainCategoryBundle\Service\CategorySearchService;

$searchService = $this->container->get(CategorySearchService::class);

// 高级搜索
$results = $searchService->advancedSearch([
    'title' => '电工',
    'level' => 2,
    'hasRequirements' => true,
    'minAge' => 18,
    'maxAge' => 60,
]);

// 智能推荐
$recommendations = $searchService->getRecommendations([
    'age' => 25,
    'industry' => '建筑施工',
    'experience' => '初级',
]);
```

### 统计分析

```php
use Tourze\TrainCategoryBundle\Service\CategoryStatisticsService;

$statisticsService = $this->container->get(CategoryStatisticsService::class);

// 获取概览统计
$overview = $statisticsService->getOverviewStatistics();

// 获取健康度报告
$healthReport = $statisticsService->getHealthReport();

// 导出统计报表
$csvData = $statisticsService->exportStatistics('csv');
```

### 导入导出

```php
use Tourze\TrainCategoryBundle\Service\CategoryImportExportService;

$importExportService = $this->container->get(CategoryImportExportService::class);

// 导出分类数据
$excelData = $importExportService->exportCategories('excel');

// 导入分类数据
$result = $importExportService->importCategories($filePath, 'excel');
```

## 实体说明

### Category 实体
- `id`: 主键
- `title`: 分类标题
- `parent`: 父分类（自关联）
- `children`: 子分类集合
- `sortNumber`: 排序号
- `createdAt`: 创建时间
- `updatedAt`: 更新时间

### CategoryRequirement 实体
- `id`: 主键
- `category`: 关联分类
- `initialTrainingHours`: 初训学时
- `refreshTrainingHours`: 复训学时
- `theoryHours`: 理论学时
- `practiceHours`: 实操学时
- `certificateValidityPeriod`: 证书有效期（月）
- `requiresPracticalExam`: 是否需要实操考试
- `requiresOnSiteTraining`: 是否需要现场培训
- `minimumAge/maximumAge`: 年龄要求
- `prerequisites`: 前置条件（JSON数组）
- `educationRequirements`: 学历要求（JSON数组）
- `healthRequirements`: 健康要求（JSON数组）
- `experienceRequirements`: 经验要求（JSON数组）
- `remarks`: 备注

## 命令行工具

### 导入标准分类

```bash
# 导入AQ8011-2023标准分类
php bin/console train-category:import-standard

# 强制覆盖现有数据
php bin/console train-category:import-standard --force
```

## EasyAdmin集成

本包提供了完整的EasyAdmin管理界面和菜单集成：

### 管理界面
- **分类管理**：`/admin/category`
- **培训要求管理**：`/admin/category-requirement`

### 菜单集成
本包自动集成到EasyAdmin菜单系统中，在后台管理界面会显示"培训分类管理"菜单，包含：
- **分类管理**：管理培训分类的树形结构
- **培训要求**：配置各分类的培训要求

菜单通过 `AdminMenu` 服务自动注册，无需手动配置。

### 管理界面功能
- 树形结构显示
- 拖拽排序
- 批量操作
- 高级筛选
- 数据导入导出

## API接口

### JSON-RPC接口

```php
// 获取工种培训分类
$procedure = new GetJobTrainingCategory();
$result = $procedure->call(['jobType' => 'electrician']);
```

## 开发指南

### 扩展分类验证

```php
use Tourze\TrainCategoryBundle\Service\CategoryValidationService;

class CustomValidationService extends CategoryValidationService
{
    public function validateCustomRule(Category $category): array
    {
        $errors = [];
        // 自定义验证逻辑
        return $errors;
    }
}
```

### 自定义搜索条件

```php
use Tourze\TrainCategoryBundle\Service\CategorySearchService;

class CustomSearchService extends CategorySearchService
{
    public function searchByCustomCriteria(array $criteria): array
    {
        // 自定义搜索逻辑
        return $this->categoryRepository->findBy($criteria);
    }
}
```

## 测试

```bash
# 运行单元测试
vendor/bin/phpunit

# 运行特定测试
vendor/bin/phpunit tests/Unit/Service/CategoryServiceTest.php

# 生成覆盖率报告
vendor/bin/phpunit --coverage-html coverage/
```

## 许可证

MIT License

## 贡献

欢迎提交Issue和Pull Request来改进这个包。

## 更新日志

### v1.0.0
- 初始版本发布
- 基础分类管理功能
- AQ8011-2023标准支持
- EasyAdmin集成

### v1.1.0
- 添加高级搜索功能
- 添加统计分析功能
- 添加导入导出功能
- 添加模块集成功能
- 完善数据填充类
