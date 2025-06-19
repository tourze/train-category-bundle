<?php

namespace Tourze\TrainCategoryBundle\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\NumericFilter;
use Tourze\TrainCategoryBundle\Entity\CategoryRequirement;

/**
 * @extends AbstractCrudController<CategoryRequirement>
 */
class CategoryRequirementCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return CategoryRequirement::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('分类培训要求')
            ->setEntityLabelInPlural('分类培训要求')
            ->setPageTitle('index', '分类培训要求列表')
            ->setPageTitle('new', '新建分类培训要求')
            ->setPageTitle('edit', '编辑分类培训要求')
            ->setPageTitle('detail', '分类培训要求详情')
            ->setHelp('index', '管理各培训分类的具体要求，包括学时、考试、年龄等要求配置。')
            ->setDefaultSort(['createTime' => 'DESC'])
            ->setSearchFields(['category.title', 'remarks']);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id', 'ID')
            ->setMaxLength(9999)
            ->hideOnForm();

        yield AssociationField::new('category', '培训分类')
            ->setRequired(true)
            ->autocomplete()
            ->setHelp('选择要配置要求的培训分类')
            ->formatValue(function ($value) {
                if ($value instanceof CategoryRequirement) {
                    return (string) $value->getCategory();
                }
                return '未选择';
            });

        // 学时配置
        yield IntegerField::new('initialTrainingHours', '初训学时')
            ->setHelp('初次培训所需的学时数')
            ->setRequired(false);

        yield IntegerField::new('refreshTrainingHours', '复训学时')
            ->setHelp('复训所需的学时数')
            ->setRequired(false);

        yield IntegerField::new('theoryHours', '理论学时')
            ->setHelp('理论培训学时数')
            ->setRequired(false);

        yield IntegerField::new('practiceHours', '实操学时')
            ->setHelp('实操培训学时数')
            ->setRequired(false);

        // 证书配置
        yield IntegerField::new('certificateValidityPeriod', '证书有效期')
            ->setHelp('证书有效期（月）')
            ->setRequired(false);

        // 考试和培训要求
        yield BooleanField::new('requiresPracticalExam', '需要实操考试')
            ->setHelp('是否要求进行实操考试');

        yield BooleanField::new('requiresOnSiteTraining', '需要现场培训')
            ->setHelp('是否要求进行现场培训');

        // 年龄要求
        yield IntegerField::new('minimumAge', '最低年龄')
            ->setHelp('参训人员最低年龄要求')
            ->setRequired(false);

        yield IntegerField::new('maximumAge', '最高年龄')
            ->setHelp('参训人员最高年龄限制')
            ->setRequired(false);

        // 详细要求（仅在详情和编辑页面显示）
        if ((bool) in_array($pageName, [Crud::PAGE_DETAIL, Crud::PAGE_EDIT, Crud::PAGE_NEW])) {
            yield TextareaField::new('prerequisites', '前置条件')
                ->setHelp('JSON格式的前置条件数组，如：["身体健康","无色盲色弱"]')
                ->hideOnIndex()
                ->formatValue(function ($value) {
                    return is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : $value;
                });

            yield TextareaField::new('educationRequirements', '学历要求')
                ->setHelp('JSON格式的学历要求数组，如：["初中及以上学历"]')
                ->hideOnIndex()
                ->formatValue(function ($value) {
                    return is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : $value;
                });

            yield TextareaField::new('healthRequirements', '健康要求')
                ->setHelp('JSON格式的健康要求数组，如：["体检合格","听力正常"]')
                ->hideOnIndex()
                ->formatValue(function ($value) {
                    return is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : $value;
                });

            yield TextareaField::new('experienceRequirements', '工作经验要求')
                ->setHelp('JSON格式的工作经验要求数组')
                ->hideOnIndex()
                ->formatValue(function ($value) {
                    return is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : $value;
                });
        }

        yield TextareaField::new('remarks', '备注说明')
            ->setHelp('其他说明信息')
            ->hideOnIndex()
            ->setRequired(false);

        // 计算字段（仅在详情页显示）
        if ($pageName === Crud::PAGE_DETAIL) {
            yield TextField::new('totalHours', '总学时')
                ->setHelp('理论学时 + 实操学时')
                ->formatValue(function ($value, $entity) {
                    if ($entity instanceof CategoryRequirement) {
                        return $entity->getTotalHours() . ' 学时';
                    }
                    return '';
                })
                ->hideOnForm();

            yield TextField::new('requirementSummary', '要求摘要')
                ->setHelp('培训要求的简要说明')
                ->formatValue(function ($value, $entity) {
                    if ($entity instanceof CategoryRequirement) {
                        return $entity->getRequirementSummary();
                    }
                    return '';
                })
                ->hideOnForm();
        }

        yield DateTimeField::new('createTime', '创建时间')
            ->hideOnForm()
            ->setFormat('yyyy-MM-dd HH:mm:ss');

        yield DateTimeField::new('updateTime', '更新时间')
            ->hideOnForm()
            ->setFormat('yyyy-MM-dd HH:mm:ss');
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->reorder(Crud::PAGE_INDEX, [Action::DETAIL, Action::EDIT, Action::DELETE]);
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(EntityFilter::new('category', '培训分类'))
            ->add(BooleanFilter::new('requiresPracticalExam', '需要实操考试'))
            ->add(BooleanFilter::new('requiresOnSiteTraining', '需要现场培训'))
            ->add(NumericFilter::new('initialTrainingHours', '初训学时'))
            ->add(NumericFilter::new('certificateValidityPeriod', '证书有效期'));
    }
} 