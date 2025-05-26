# Train Category Bundle

培训分类管理 Bundle，提供培训资源的分类管理功能。

## 功能特性

- 支持树形分类结构（父子级关系）
- 与题库系统集成
- 提供 EasyAdmin 管理界面
- 支持分类排序

## 实体说明

### Category 实体

- `id`: 雪花算法生成的唯一ID
- `title`: 分类名称
- `parent`: 父级分类（支持树形结构）
- `children`: 子级分类集合
- `banks`: 关联的题库集合
- `sortNumber`: 排序值（数值越大排序越靠前）
- 时间戳字段：`createTime`、`updateTime`
- 用户追踪字段：`createdBy`、`updatedBy`

## 管理界面

Bundle 提供了完整的 EasyAdmin CRUD 控制器：

- `CategoryCrudController`: 分类管理控制器
- 支持树形结构显示和编辑
- 提供搜索和过滤功能

## 服务

- `AdminMenu`: 提供管理菜单配置

## 安装和配置

1. 确保已安装 EasyAdmin Bundle
2. 在应用中注册此 Bundle
3. 运行数据库迁移创建相关表

## 使用示例

```php
// 获取默认分类
$categoryRepository = $entityManager->getRepository(Category::class);
$defaultCategory = $categoryRepository->getDefaultCategory();

// 创建新分类
$category = new Category();
$category->setTitle('新分类');
$category->setParent($parentCategory);
$category->setSortNumber(100);
```
