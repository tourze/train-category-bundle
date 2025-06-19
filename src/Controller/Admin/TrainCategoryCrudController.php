<?php

namespace Tourze\TrainCategoryBundle\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use Tourze\TrainCategoryBundle\Entity\Category;

/**
 * @extends AbstractCrudController<Category>
 */
class TrainCategoryCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Category::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('培训分类')
            ->setEntityLabelInPlural('培训分类')
            ->setPageTitle('index', '培训分类列表')
            ->setPageTitle('new', '新建培训分类')
            ->setPageTitle('edit', '编辑培训分类')
            ->setPageTitle('detail', '培训分类详情')
            ->setHelp('index', '管理培训资源的分类信息，支持树形结构。一级是人员类型，二级是行业类别。')
            ->setDefaultSort(['sortNumber' => 'DESC', 'id' => 'DESC'])
            ->setSearchFields(['title']);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id', 'ID')
            ->setMaxLength(9999)
            ->hideOnForm();

        yield TextField::new('title', '分类名称')
            ->setRequired(true)
            ->setMaxLength(100)
            ->setHelp('分类的显示名称，最大100个字符');

        yield AssociationField::new('parent', '上级分类')
            ->setRequired(false)
            ->autocomplete()
            ->setHelp('选择上级分类，留空表示顶级分类')
            ->formatValue(function ($value) {
                if ($value instanceof Category) {
                    return (string) $value;
                }
                return '顶级分类';
            });

        yield IntegerField::new('sortNumber', '排序值')
            ->setHelp('数值越大排序越靠前，默认为0')
            ->setRequired(false);

        yield TextField::new('createdBy', '创建人')
            ->hideOnForm()
            ->onlyOnDetail();

        yield TextField::new('updatedBy', '更新人')
            ->hideOnForm()
            ->onlyOnDetail();

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
            ->add(TextFilter::new('title', '分类名称'))
            ->add(EntityFilter::new('parent', '上级分类'));
    }
}
