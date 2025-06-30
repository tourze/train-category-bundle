<?php

namespace Tourze\TrainCategoryBundle\Procedure;

use Tourze\JsonRPC\Core\Attribute\MethodDoc;
use Tourze\JsonRPC\Core\Attribute\MethodExpose;
use Tourze\JsonRPC\Core\Attribute\MethodParam;
use Tourze\JsonRPC\Core\Exception\ApiException;
use Tourze\JsonRPC\Core\Procedure\BaseProcedure;
use Tourze\TrainCategoryBundle\Repository\CategoryRepository;

#[MethodDoc(summary: '获取分类信息')]
#[MethodExpose(method: 'GetJobTrainingCategory')]
class GetJobTrainingCategory extends BaseProcedure
{
    #[MethodParam(description: '上级分类ID')]
    public ?string $parentId = null;

    public function __construct(
        private readonly CategoryRepository $categoryRepository,
    ) {
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function execute(): array
    {
        $parent = null;
        if (null !== $this->parentId) {
            $parent = $this->categoryRepository->findOneBy(['id' => $this->parentId]);
            if ($parent === null) {
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
