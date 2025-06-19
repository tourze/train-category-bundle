<?php

namespace Tourze\TrainCategoryBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Tourze\TrainCategoryBundle\Entity\Category;
use Tourze\TrainCategoryBundle\Repository\CategoryRepository;

/**
 * 分类导入导出服务类
 * 
 * 提供分类数据的导入导出功能，支持多种格式
 */
class CategoryImportExportService
{
    public function __construct(
        private readonly CategoryRepository $categoryRepository,
        private readonly CategoryService $categoryService,
        private readonly CategoryRequirementService $requirementService,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * 导出分类数据
     * @param array<string, mixed> $options
     * @return array<string, mixed>
     */
    public function exportCategories(array $options = []): array
    {
        $formatValue = $options['format'] ?? 'json';
        $format = is_string($formatValue) ? $formatValue : 'json';
        $includeRequirements = (bool) ($options['includeRequirements'] ?? true);
        $includeHierarchy = (bool) ($options['includeHierarchy'] ?? true);
        $categoryIds = $options['categoryIds'] ?? null;

        // 获取要导出的分类
        if ($categoryIds !== null) {
            $categories = $this->categoryRepository->findBy(['id' => $categoryIds]);
        } else {
            $categories = $this->categoryRepository->findAll();
        }

        // 构建导出数据
        $exportData = [
            'metadata' => [
                'exported_at' => new \DateTime(),
                'total_categories' => count($categories),
                'format_version' => '1.0',
                'includes_requirements' => $includeRequirements,
                'includes_hierarchy' => $includeHierarchy,
            ],
            'categories' => [],
        ];

        if ($includeHierarchy) {
            /** @var array<int, Category> $categories */
            $exportData['categories'] = $this->buildHierarchicalExport($categories, $includeRequirements);
        } else {
            /** @var array<int, Category> $categories */
            $exportData['categories'] = $this->buildFlatExport($categories, $includeRequirements);
        }

        // 根据格式处理数据
        return $this->formatExportData($exportData, $format);
    }

    /**
     * 导入分类数据
     * @param array<string, mixed> $data
     * @param array<string, mixed> $options
     * @return array<string, mixed>
     */
    public function importCategories(array $data, array $options = []): array
    {
        $dryRun = (bool) ($options['dryRun'] ?? false);
        $overwrite = (bool) ($options['overwrite'] ?? false);
        $validateOnly = (bool) ($options['validateOnly'] ?? false);

        $result = [
            'success' => false,
            'imported_count' => 0,
            'skipped_count' => 0,
            'error_count' => 0,
            'errors' => [],
            'warnings' => [],
        ];

        try {
            // 验证导入数据格式
            $validationResult = $this->validateImportData($data);
            if ($validationResult['valid'] === false) {
                $result['errors'] = $validationResult['errors'];
                return $result;
            }

            if ($validateOnly) {
                $result['success'] = true;
                return $result;
            }

            // 开始事务
            $this->entityManager->beginTransaction();

            // 处理导入数据
            if (isset($data['categories']) && is_array($data['categories'])) {
                /** @var array<int, array<string, mixed>> $categoriesData */
                $categoriesData = $data['categories'];
                $importResult = $this->processImportData($categoriesData, $dryRun, $overwrite);
                $result = array_merge($result, $importResult);
            }

            if ($dryRun === false && $result['error_count'] === 0) {
                $this->entityManager->commit();
                $result['success'] = true;
            } else {
                $this->entityManager->rollback();
                if ($dryRun) {
                    $result['success'] = true;
                }
            }

        } catch (\Throwable $e) {
            $this->entityManager->rollback();
            $result['errors'][] = '导入过程中发生错误：' . $e->getMessage();
            $result['error_count']++;
        }

        return $result;
    }

    /**
     * 导出为Excel格式
     * @param array<string, mixed> $options
     * @return array<string, mixed>
     */
    public function exportToExcel(array $options = []): array
    {
        $categories = $this->categoryRepository->findAll();
        $includeRequirements = (bool) ($options['includeRequirements'] ?? true);

        $excelData = [
            'sheets' => [
                'categories' => [
                    'headers' => ['ID', '分类名称', '父分类ID', '父分类名称', '排序值', '层级', '创建时间'],
                    'data' => [],
                ],
            ],
        ];

        // 添加培训要求表
        if ($includeRequirements) {
            $excelData['sheets']['requirements'] = [
                'headers' => [
                    'ID', '分类ID', '分类名称', '初训学时', '复训学时', '理论学时', '实操学时',
                    '证书有效期', '需要实操考试', '需要现场培训', '最低年龄', '最高年龄', '备注'
                ],
                'data' => [],
            ];
        }

        foreach ($categories as $category) {
            // 分类数据
            $level = $this->calculateCategoryLevel($category);
            $excelData['sheets']['categories']['data'][] = [
                $category->getId(),
                $category->getTitle(),
                $category->getParent() !== null ? $category->getParent()->getId() : '',
                $category->getParent() !== null ? $category->getParent()->getTitle() : '',
                $category->getSortNumber(),
                $level,
                $category->getCreateTime()->format('Y-m-d H:i:s'),
            ];

            // 培训要求数据
            if ($includeRequirements) {
                $requirement = $this->requirementService->getCategoryRequirement($category);
                if ($requirement !== null) {
                    $excelData['sheets']['requirements']['data'][] = [
                        $requirement->getId(),
                        $category->getId(),
                        $category->getTitle(),
                        $requirement->getInitialTrainingHours(),
                        $requirement->getRefreshTrainingHours(),
                        $requirement->getTheoryHours(),
                        $requirement->getPracticeHours(),
                        $requirement->getCertificateValidityPeriod(),
                        $requirement->isRequiresPracticalExam() ? '是' : '否',
                        $requirement->isRequiresOnSiteTraining() ? '是' : '否',
                        $requirement->getMinimumAge(),
                        $requirement->getMaximumAge(),
                        $requirement->getRemarks() ?? '',
                    ];
                }
            }
        }

        return $excelData;
    }

    /**
     * 从Excel导入
     * @param array<string, mixed> $excelData
     * @param array<string, mixed> $options
     * @return array<string, mixed>
     */
    public function importFromExcel(array $excelData, array $options = []): array
    {
        $result = [
            'success' => false,
            'imported_count' => 0,
            'error_count' => 0,
            'errors' => [],
        ];

        try {
            $this->entityManager->beginTransaction();

            // 处理分类数据
            if (isset($excelData['categories'])) {
                $categoryResult = $this->importCategoriesFromExcel($excelData['categories']);
                $result['imported_count'] += $categoryResult['imported_count'];
                $result['error_count'] += $categoryResult['error_count'];
                $result['errors'] = array_merge($result['errors'], $categoryResult['errors']);
            }

            // 处理培训要求数据
            if (isset($excelData['requirements'])) {
                $requirementResult = $this->importRequirementsFromExcel($excelData['requirements']);
                $result['error_count'] += $requirementResult['error_count'];
                $result['errors'] = array_merge($result['errors'], $requirementResult['errors']);
            }

            if ($result['error_count'] === 0) {
                $this->entityManager->commit();
                $result['success'] = true;
            } else {
                $this->entityManager->rollback();
            }

        } catch (\Throwable $e) {
            $this->entityManager->rollback();
            $result['errors'][] = '导入过程中发生错误：' . $e->getMessage();
            $result['error_count']++;
        }

        return $result;
    }

    /**
     * 导出分类模板
     * @return array<string, mixed>
     */
    public function exportTemplate(string $format = 'excel'): array
    {
        $template = [
            'categories' => [
                'headers' => ['分类名称*', '父分类名称', '排序值', '备注'],
                'sample_data' => [
                    ['培训类别', '', '1000', '一级分类'],
                    ['特种作业人员培训', '培训类别', '900', '二级分类'],
                    ['电工作业', '特种作业人员培训', '800', '三级分类'],
                ],
                'instructions' => [
                    '1. 带*号的字段为必填项',
                    '2. 父分类名称为空表示顶级分类',
                    '3. 排序值越大排序越靠前',
                    '4. 请确保父分类在子分类之前创建',
                ],
            ],
            'requirements' => [
                'headers' => [
                    '分类名称*', '初训学时', '复训学时', '理论学时', '实操学时',
                    '证书有效期(月)', '需要实操考试', '需要现场培训', '最低年龄', '最高年龄', '备注'
                ],
                'sample_data' => [
                    ['电工作业', '80', '24', '56', '24', '36', '是', '是', '18', '60', '特种作业'],
                ],
                'instructions' => [
                    '1. 分类名称必须与分类表中的名称完全一致',
                    '2. 需要实操考试和需要现场培训填写"是"或"否"',
                    '3. 年龄范围应符合实际要求',
                ],
            ],
        ];

        return $template;
    }

    /**
     * 批量导出指定分类及其子分类
     * @param array<string, mixed> $options
     * @return array<string, mixed>
     */
    public function exportCategoryBranch(Category $rootCategory, array $options = []): array
    {
        $includeRequirements = (bool) ($options['includeRequirements'] ?? true);
        $formatValue = $options['format'] ?? 'json';
        $format = is_string($formatValue) ? $formatValue : 'json';

        // 获取分类树
        $categoryTree = $this->buildCategoryBranch($rootCategory, $includeRequirements);

        $exportData = [
            'metadata' => [
                'exported_at' => new \DateTime(),
                'root_category' => $rootCategory->getTitle(),
                'format_version' => '1.0',
                'includes_requirements' => $includeRequirements,
            ],
            'category_tree' => $categoryTree,
        ];

        return $this->formatExportData($exportData, $format);
    }

    /**
     * 构建层级导出数据
     * @param array<int, Category> $categories
     * @return array<int, array<string, mixed>>
     */
    private function buildHierarchicalExport(array $categories, bool $includeRequirements): array
    {
        $categoryMap = [];
        $rootCategories = [];

        // 构建分类映射
        foreach ($categories as $category) {
            $categoryData = $this->buildCategoryData($category, $includeRequirements);
            $categoryMap[$category->getId()] = $categoryData;

            if ($category->getParent() === null) {
                $rootCategories[] = &$categoryMap[$category->getId()];
            }
        }

        // 构建层级关系
        foreach ($categories as $category) {
            if ($category->getParent() !== null) {
                $parentId = $category->getParent()->getId();
                if (isset($categoryMap[$parentId])) {
                    $categoryMap[$parentId]['children'][] = &$categoryMap[$category->getId()];
                }
            }
        }

        return $rootCategories;
    }

    /**
     * 构建扁平导出数据
     * @param array<int, Category> $categories
     * @return array<int, array<string, mixed>>
     */
    private function buildFlatExport(array $categories, bool $includeRequirements): array
    {
        $exportData = [];

        foreach ($categories as $category) {
            $categoryData = $this->buildCategoryData($category, $includeRequirements);
            $categoryData['parent_id'] = $category->getParent() !== null ? $category->getParent()->getId() : null;
            $categoryData['parent_title'] = $category->getParent() !== null ? $category->getParent()->getTitle() : null;
            $categoryData['level'] = $this->calculateCategoryLevel($category);
            
            unset($categoryData['children']); // 扁平格式不需要children
            $exportData[] = $categoryData;
        }

        return $exportData;
    }

    /**
     * 构建单个分类数据
     * @return array<string, mixed>
     */
    private function buildCategoryData(Category $category, bool $includeRequirements): array
    {
        $data = [
            'id' => $category->getId(),
            'title' => $category->getTitle(),
            'sort_number' => $category->getSortNumber(),
            'create_time' => $category->getCreateTime()->format('Y-m-d H:i:s'),
            'update_time' => $category->getUpdateTime()->format('Y-m-d H:i:s'),
            'children' => [],
        ];

        if ($includeRequirements) {
            $requirement = $this->requirementService->getCategoryRequirement($category);
            if ($requirement !== null) {
                $data['requirement'] = [
                    'initial_training_hours' => $requirement->getInitialTrainingHours(),
                    'refresh_training_hours' => $requirement->getRefreshTrainingHours(),
                    'theory_hours' => $requirement->getTheoryHours(),
                    'practice_hours' => $requirement->getPracticeHours(),
                    'certificate_validity_period' => $requirement->getCertificateValidityPeriod(),
                    'requires_practical_exam' => $requirement->isRequiresPracticalExam(),
                    'requires_onsite_training' => $requirement->isRequiresOnSiteTraining(),
                    'minimum_age' => $requirement->getMinimumAge(),
                    'maximum_age' => $requirement->getMaximumAge(),
                    'prerequisites' => $requirement->getPrerequisites(),
                    'education_requirements' => $requirement->getEducationRequirements(),
                    'health_requirements' => $requirement->getHealthRequirements(),
                    'experience_requirements' => $requirement->getExperienceRequirements(),
                    'remarks' => $requirement->getRemarks(),
                ];
            }
        }

        return $data;
    }

    /**
     * 构建分类分支
     * @return array<string, mixed>
     */
    private function buildCategoryBranch(Category $category, bool $includeRequirements): array
    {
        $data = $this->buildCategoryData($category, $includeRequirements);

        foreach ($category->getChildren() as $child) {
            $data['children'][] = $this->buildCategoryBranch($child, $includeRequirements);
        }

        return $data;
    }

    /**
     * 格式化导出数据
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function formatExportData(array $data, string $format): array
    {
        switch ($format) {
            case 'json':
                return [
                    'content' => json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT),
                    'filename' => 'categories_' . date('Y-m-d_H-i-s') . '.json',
                    'content_type' => 'application/json',
                ];

            case 'xml':
                $xml = $this->arrayToXml($data);
                return [
                    'content' => $xml,
                    'filename' => 'categories_' . date('Y-m-d_H-i-s') . '.xml',
                    'content_type' => 'application/xml',
                ];

            case 'csv':
                $csv = $this->arrayToCsv($data['categories']);
                return [
                    'content' => $csv,
                    'filename' => 'categories_' . date('Y-m-d_H-i-s') . '.csv',
                    'content_type' => 'text/csv',
                ];

            default:
                return $data;
        }
    }

    /**
     * 验证导入数据
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function validateImportData(array $data): array
    {
        $result = ['valid' => true, 'errors' => []];

        // 检查基本结构
        if (!isset($data['categories'])) {
            $result['valid'] = false;
            $result['errors'][] = '缺少categories字段';
            return $result;
        }

        // 验证每个分类数据
        foreach ($data['categories'] as $index => $categoryData) {
            if (!isset($categoryData['title']) || empty($categoryData['title'])) {
                $result['valid'] = false;
                $result['errors'][] = "第{$index}个分类缺少title字段";
            }

            if (isset($categoryData['requirement'])) {
                $requirementErrors = $this->validateRequirementData($categoryData['requirement']);
                if (!empty($requirementErrors)) {
                    $result['valid'] = false;
                    $result['errors'] = array_merge($result['errors'], $requirementErrors);
                }
            }
        }

        return $result;
    }

    /**
     * 验证培训要求数据
     * @param array<string, mixed> $requirementData
     * @return array<int, string>
     */
    private function validateRequirementData(array $requirementData): array
    {
        $errors = [];

        $numericFields = [
            'initial_training_hours', 'refresh_training_hours', 'theory_hours', 
            'practice_hours', 'certificate_validity_period', 'minimum_age', 'maximum_age'
        ];

        foreach ($numericFields as $field) {
            if (isset($requirementData[$field]) && !is_numeric($requirementData[$field])) {
                $errors[] = "培训要求字段{$field}必须为数字";
            }
        }

        $booleanFields = ['requires_practical_exam', 'requires_onsite_training'];
        foreach ($booleanFields as $field) {
            if (isset($requirementData[$field]) && !is_bool($requirementData[$field])) {
                $errors[] = "培训要求字段{$field}必须为布尔值";
            }
        }

        return $errors;
    }

    /**
     * 处理导入数据
     * @param array<int, array<string, mixed>> $categoriesData
     * @return array<string, mixed>
     */
    private function processImportData(array $categoriesData, bool $dryRun, bool $overwrite): array
    {
        $result = [
            'imported_count' => 0,
            'skipped_count' => 0,
            'error_count' => 0,
            'errors' => [],
        ];

        foreach ($categoriesData as $categoryData) {
            try {
                $processResult = $this->processCategory($categoryData, $dryRun, $overwrite);
                if ($processResult['success']) {
                    $result['imported_count']++;
                } else {
                    $result['skipped_count']++;
                }
            } catch (\Throwable $e) {
                $result['error_count']++;
                $result['errors'][] = "处理分类 '{$categoryData['title']}' 时发生错误：" . $e->getMessage();
            }
        }

        return $result;
    }

    /**
     * 处理单个分类
     * @param array<string, mixed> $categoryData
     * @return array<string, bool|string>
     */
    private function processCategory(array $categoryData, bool $dryRun, bool $overwrite): array
    {
        $title = $categoryData['title'];
        $existingCategory = $this->categoryService->findByTitle($title);

        if ($existingCategory !== null && !$overwrite) {
            return ['success' => false, 'message' => '分类已存在'];
        }

        if (!$dryRun) {
            // 查找或创建父分类
            $parent = null;
            if (isset($categoryData['parent_title']) && !empty($categoryData['parent_title'])) {
                $parent = $this->categoryService->findByTitle($categoryData['parent_title']);
            }

            // 创建或更新分类
            if ($existingCategory !== null && $overwrite) {
                $category = $this->categoryService->updateCategory($existingCategory, [
                    'title' => $title,
                    'parent' => $parent,
                    'sortNumber' => $categoryData['sort_number'] ?? 0,
                ]);
            } else {
                $category = $this->categoryService->createCategory(
                    $title,
                    $parent,
                    $categoryData['sort_number'] ?? 0
                );
            }

            // 处理培训要求
            if (isset($categoryData['requirement'])) {
                $this->requirementService->setCategoryRequirement($category, $categoryData['requirement']);
            }
        }

        return ['success' => true, 'message' => '导入成功'];
    }

    /**
     * 从Excel导入分类
     * @param array<int, array<int, mixed>> $categoriesData
     * @return array<string, mixed>
     */
    private function importCategoriesFromExcel(array $categoriesData): array
    {
        $result = ['imported_count' => 0, 'error_count' => 0, 'errors' => []];

        foreach ($categoriesData as $row) {
            try {
                if (empty($row[0])) continue; // 跳过空行

                $title = $row[0];
                $parentTitle = $row[1] ?? null;
                $sortNumber = (int) ($row[2] ?? 0);

                $parent = null;
                if ($parentTitle !== null) {
                    $parent = $this->categoryService->findByTitle($parentTitle);
                    if ($parent === null) {
                        $result['errors'][] = "分类 '{$title}' 的父分类 '{$parentTitle}' 不存在";
                        $result['error_count']++;
                        continue;
                    }
                }

                $this->categoryService->createCategory($title, $parent, $sortNumber);
                $result['imported_count']++;

            } catch (\Throwable $e) {
                $result['error_count']++;
                $result['errors'][] = "导入分类时发生错误：" . $e->getMessage();
            }
        }

        return $result;
    }

    /**
     * 从Excel导入培训要求
     * @param array<int, array<int, mixed>> $requirementsData
     * @return array<string, mixed>
     */
    private function importRequirementsFromExcel(array $requirementsData): array
    {
        $result = ['error_count' => 0, 'errors' => []];

        foreach ($requirementsData as $row) {
            try {
                if (empty($row[0])) continue; // 跳过空行

                $categoryTitle = $row[0];
                $category = $this->categoryService->findByTitle($categoryTitle);

                if ($category === null) {
                    $result['errors'][] = "分类 '{$categoryTitle}' 不存在";
                    $result['error_count']++;
                    continue;
                }

                $requirements = [
                    'initialTrainingHours' => (int) ($row[1] ?? 0),
                    'refreshTrainingHours' => (int) ($row[2] ?? 0),
                    'theoryHours' => (int) ($row[3] ?? 0),
                    'practiceHours' => (int) ($row[4] ?? 0),
                    'certificateValidityPeriod' => (int) ($row[5] ?? 36),
                    'requiresPracticalExam' => ($row[6] ?? '') === '是',
                    'requiresOnSiteTraining' => ($row[7] ?? '') === '是',
                    'minimumAge' => (int) ($row[8] ?? 18),
                    'maximumAge' => (int) ($row[9] ?? 60),
                    'remarks' => $row[10] ?? null,
                ];

                $this->requirementService->setCategoryRequirement($category, $requirements);

            } catch (\Throwable $e) {
                $result['error_count']++;
                $result['errors'][] = "导入培训要求时发生错误：" . $e->getMessage();
            }
        }

        return $result;
    }

    /**
     * 计算分类层级
     */
    private function calculateCategoryLevel(Category $category): int
    {
        $level = 1;
        $current = $category;
        while ($current->getParent() !== null) {
            $level++;
            $current = $current->getParent();
        }
        return $level;
    }

    /**
     * 数组转XML
     * @param array<string, mixed> $data
     */
    private function arrayToXml(array $data): string
    {
        $xml = new \SimpleXMLElement('<root/>');
        $this->arrayToXmlRecursive($data, $xml);
        return $xml->asXML();
    }

    /**
     * 递归转换数组到XML
     * @param array<string, mixed> $data
     */
    private function arrayToXmlRecursive(array $data, \SimpleXMLElement $xml): void
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $child = $xml->addChild($key);
                $this->arrayToXmlRecursive($value, $child);
            } else {
                $xml->addChild($key, htmlspecialchars($value));
            }
        }
    }

    /**
     * 数组转CSV
     * @param array<int, array<string, mixed>> $data
     */
    private function arrayToCsv(array $data): string
    {
        if (empty($data)) {
            return '';
        }

        $output = fopen('php://temp', 'r+');
        
        // 写入表头
        $headers = array_keys($data[0]);
        fputcsv($output, $headers);

        // 写入数据
        foreach ($data as $row) {
            fputcsv($output, array_values($row));
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv;
    }
} 