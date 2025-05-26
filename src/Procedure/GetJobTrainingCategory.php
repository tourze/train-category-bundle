<?php

namespace Tourze\TrainCategoryBundle\Procedure;

use Tourze\JsonRPC\Core\Attribute\MethodDoc;
use Tourze\JsonRPC\Core\Attribute\MethodExpose;
use Tourze\JsonRPC\Core\Attribute\MethodParam;
use Tourze\JsonRPC\Core\Exception\ApiException;
use Tourze\JsonRPC\Core\Procedure\BaseProcedure;
use Tourze\TrainCategoryBundle\Repository\CategoryRepository;

#[MethodDoc('获取分类信息')]
#[MethodExpose('GetJobTrainingCategory')]
class GetJobTrainingCategory extends BaseProcedure
{
    #[MethodParam('上级分类ID')]
    public ?string $parentId = null;

    public function __construct(
        private readonly CategoryRepository $categoryRepository,
    ) {
    }

    public function execute(): array
    {
        $parent = null;
        if (null !== $this->parentId) {
            $parent = $this->categoryRepository->findOneBy(['id' => $this->parentId]);
            if (!$parent) {
                throw new ApiException('找不到上级目录');
            }
        }

        $categories = $this->categoryRepository->findBy([
            'parent' => $parent,
        ], [
            'sortNumber' => 'DESC',
            'id' => 'ASC',
        ]);

        $result = [];
        foreach ($categories as $category) {
            $result[] = $category->retrieveApiArray();
        }

        return $result;
    }
}
