<?php

namespace Tourze\TrainCategoryBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Tourze\TrainCategoryBundle\Entity\Category;

/**
 * @method Category|null find($id, $lockMode = null, $lockVersion = null)
 * @method Category|null findOneBy(array $criteria, array $orderBy = null)
 * @method Category[]    findAll()
 * @method Category[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CategoryRepository extends ServiceEntityRepository
{

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Category::class);
    }

    public function getDefaultCategory(): Category
    {
        $title = '未分类';
        $category = $this->findOneBy(['title' => $title]);
        if (!$category) {
            $category = new Category();
            $category->setTitle($title);
            $this->getEntityManager()->persist($category);
            $this->getEntityManager()->flush();
        }

        return $category;
    }
}
