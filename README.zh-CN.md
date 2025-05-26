# 培训分类管理包 (Train Category Bundle)

一个基于 Symfony 的培训分类管理包，专门用于安全生产培训资源的分类管理。

## 功能特性

- **树形分类结构**：支持无限层级的分类管理
- **真实数据结构**：基于 `job_training_category.sql` 的真实数据结构
- **培训要求管理**：为每个分类配置详细的培训要求
- **EasyAdmin 集成**：提供完整的后台管理界面
- **高级搜索**：支持多维度搜索和筛选
- **统计分析**：提供详细的分类使用统计和报表
- **数据导入导出**：支持分类数据的批量导入导出
- **标准化支持**：符合 AQ8011-2023 安全生产培训标准

## 分类结构

基于真实的 `job_training_category.sql` 数据，包含以下主要分类：

### 一级分类
- **主要负责人** (sort_number: 1000)
- **特种作业人员** (sort_number: 2000)  
- **安全生产管理人员** (sort_number: 3000)
- **未分类** (sort_number: 0)

### 二级分类

#### 主要负责人
- 危险化学品
- 金属非金属矿山
- 石油天然气开采
- 烟花爆竹
- 金属冶炼
- 非高危企业

#### 特种作业人员
- 电工作业
- 焊接与热切割作业
- 高处作业
- 制冷与空调作业
- 金属非金属矿山安全作业
- 石油天然气安全作业
- 冶金(有色)生产安全作业
- 危险化学品安全作业
- 烟花爆竹安全作业

#### 安全生产管理人员
- 危险化学品
- 金属非金属矿山
- 石油天然气开采
- 烟花爆竹
- 金属冶炼
- 非高危企业

### 三级分类

#### 电工作业
- 低压电工作业
- 高压电工作业
- 电力电缆作业
- 继电保护作业
- 电气试验作业
- 防爆电气作业

#### 危险化学品安全作业
- 光气及光气化工艺
- 氯碱电解工艺
- 氯化工艺
- 硝化工艺
- 合成氨工艺
- 裂解(裂化)工艺
- 氟化工艺
- 加氢工艺
- 重氮化工艺
- 氧化工艺
- 过氧化工艺
- 胺基化工艺
- 磺化工艺
- 聚合工艺
- 烷基化工艺
- 化工自动化控制仪表

#### 金属非金属矿山安全作业
- 金属非金属矿井通风作业
- 尾矿作业
- 安全检查作业(露天矿山)
- 安全检查作业(小型露天采石场)
- 安全检查作业(地下矿山)
- 提升机操作作业
- 支柱作业
- 井下电气作业
- 排水作业
- 爆破作业

## 安装

```bash
composer require tourze/train-category-bundle
```

## 配置

### 1. 注册 Bundle

```php
// config/bundles.php
return [
    // ...
    Tourze\TrainCategoryBundle\TrainCategoryBundle::class => ['all' => true],
];
```

### 2. 配置路由

```yaml
# config/routes.yaml
train_category:
    resource: '@TrainCategoryBundle/Resources/config/routes.yaml'
```

### 3. 更新数据库

```bash
php bin/console doctrine:migrations:diff
php bin/console doctrine:migrations:migrate
```

## 数据初始化

### 使用 DataFixtures

#### 基础分类数据
```bash
# 加载基础分类结构（主要负责人、特种作业人员、安全生产管理人员及其子分类）
php bin/console doctrine:fixtures:load --group=production --append
```

#### 详细分类数据
```bash
# 加载详细的三级分类数据
php bin/console train-category:load-real-data --detailed
```

#### 培训要求数据
```bash
# 同时加载培训要求数据
php bin/console train-category:load-real-data --detailed --with-requirements
```

### 使用专用命令

```bash
# 加载基于真实数据的分类结构
php bin/console train-category:load-real-data

# 选项说明：
# --append              追加数据而不是清空现有数据
# --detailed            加载详细的三级分类数据
# --with-requirements   同时加载培训要求数据
```

### 导入标准分类

```bash
# 导入 AQ8011-2023 标准分类（如果需要标准化数据）
php bin/console train-category:import-standard --preview
php bin/console train-category:import-standard --force
```

## 使用示例

### 基本操作

```php
use Tourze\TrainCategoryBundle\Service\CategoryService;

// 获取服务
$categoryService = $container->get(CategoryService::class);

// 创建分类
$category = $categoryService->createCategory('新分类', $parentCategory, 100);

// 更新分类
$categoryService->updateCategory($category, ['title' => '更新后的分类名']);

// 删除分类
$categoryService->deleteCategory($category);

// 获取分类树
$tree = $categoryService->getCategoryTree();

// 获取分类路径
$path = $categoryService->getCategoryPath($category);
```

### 高级搜索

```php
use Tourze\TrainCategoryBundle\Service\CategorySearchService;

$searchService = $container->get(CategorySearchService::class);

// 高级搜索
$results = $searchService->advancedSearch([
    'keyword' => '电工',
    'level' => 2,
    'hasRequirements' => true,
    'minHours' => 72,
]);

// 智能推荐
$recommendations = $searchService->recommendCategories($userId, 5);

// 分面搜索
$facets = $searchService->facetedSearch('安全作业');
```

### 统计分析

```php
use Tourze\TrainCategoryBundle\Service\CategoryStatisticsService;

$statsService = $container->get(CategoryStatisticsService::class);

// 获取概览统计
$overview = $statsService->getOverviewStatistics();

// 获取层级分布
$distribution = $statsService->getLevelDistribution();

// 生成健康度报告
$healthReport = $statsService->generateHealthReport();

// 导出统计报表
$report = $statsService->exportStatisticsReport('excel');
```

### 培训要求管理

```php
use Tourze\TrainCategoryBundle\Service\CategoryRequirementService;

$requirementService = $container->get(CategoryRequirementService::class);

// 创建培训要求
$requirement = $requirementService->createRequirement($category, [
    'initialTrainingHours' => 72,
    'refreshTrainingHours' => 24,
    'certificateValidityPeriod' => 36,
    'requiresPracticalExam' => true,
]);

// 检查用户资格
$eligible = $requirementService->checkUserEligibility($requirement, $user);

// 验证培训要求
$validation = $requirementService->validateRequirement($requirement);
```

## API 接口

### 分类管理

```http
GET    /api/categories              # 获取分类列表
POST   /api/categories              # 创建分类
GET    /api/categories/{id}         # 获取分类详情
PUT    /api/categories/{id}         # 更新分类
DELETE /api/categories/{id}         # 删除分类
GET    /api/categories/tree         # 获取分类树
GET    /api/categories/{id}/path    # 获取分类路径
```

### 搜索接口

```http
GET    /api/categories/search       # 搜索分类
POST   /api/categories/advanced-search  # 高级搜索
GET    /api/categories/recommend    # 智能推荐
GET    /api/categories/popular      # 热门分类
```

### 统计接口

```http
GET    /api/categories/statistics   # 获取统计数据
GET    /api/categories/health       # 健康度报告
GET    /api/categories/export       # 导出报表
```

## 管理界面

访问 `/admin` 进入 EasyAdmin 管理界面，可以进行：

- 分类的增删改查
- 培训要求配置
- 数据统计查看
- 批量操作

## 测试

```bash
# 运行所有测试
vendor/bin/phpunit

# 运行特定测试
vendor/bin/phpunit tests/Unit/Entity/CategoryTest.php

# 生成测试覆盖率报告
vendor/bin/phpunit --coverage-html coverage/
```

## 开发

### 目录结构

```
src/
├── Command/                 # 控制台命令
├── Controller/             # 控制器
│   └── Admin/             # EasyAdmin 控制器
├── DataFixtures/          # 数据填充
├── DependencyInjection/   # 依赖注入
├── Entity/                # 实体类
├── Repository/            # 数据仓库
├── Resources/             # 资源文件
│   └── config/           # 配置文件
└── Service/               # 服务类
```

### 扩展开发

1. **自定义分类验证**：继承 `CategoryValidationService`
2. **自定义搜索逻辑**：扩展 `CategorySearchService`
3. **自定义统计报表**：扩展 `CategoryStatisticsService`
4. **自定义导入导出**：扩展 `CategoryImportExportService`

## 许可证

MIT License

## 贡献

欢迎提交 Issue 和 Pull Request！

## 更新日志

### v1.0.0
- 初始版本发布
- 基础分类管理功能
- EasyAdmin 集成
- 基本的 CRUD 操作

### v1.1.0
- 添加培训要求管理
- 实现分类验证服务
- 支持 AQ8011-2023 标准

### v1.2.0
- 添加高级搜索功能
- 实现统计分析服务
- 支持数据导入导出

### v1.3.0 (当前版本)
- **重大更新**：基于真实 SQL 数据重构分类结构
- 新增 `LoadRealDataCommand` 命令
- 更新 DataFixtures 以反映真实数据结构
- 完善三级分类体系
- 优化测试覆盖率（59个测试，151个断言，100%通过）