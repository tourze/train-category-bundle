<?php

namespace Tourze\TrainCategoryBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Tourze\TrainCategoryBundle\Entity\Category;
use Tourze\TrainCategoryBundle\Entity\CategoryRequirement;

/**
 * 分类培训要求仓储类
 * @extends ServiceEntityRepository<CategoryRequirement>
 */
class CategoryRequirementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CategoryRequirement::class);
    }

    /**
     * 根据分类查找要求
     */
    public function findByCategory(Category $category): ?CategoryRequirement
    {
        return $this->findOneBy(['category' => $category]);
    }

    /**
     * 查找需要实操考试的分类要求
     * @return array<int, CategoryRequirement>
     */
    public function findRequiringPracticalExam(): array
    {
        return $this->findBy(['requiresPracticalExam' => true]);
    }

    /**
     * 查找需要现场培训的分类要求
     * @return array<int, CategoryRequirement>
     */
    public function findRequiringOnSiteTraining(): array
    {
        return $this->findBy(['requiresOnSiteTraining' => true]);
    }

    /**
     * 根据学时范围查找要求
     * @return array<int, CategoryRequirement>
     */
    public function findByHoursRange(int $minHours, int $maxHours): array
    {
        $qb = $this->createQueryBuilder('cr');
        
        $qb->where('(cr.theoryHours + cr.practiceHours) BETWEEN :minHours AND :maxHours')
           ->setParameter('minHours', $minHours)
           ->setParameter('maxHours', $maxHours)
           ->orderBy('cr.theoryHours + cr.practiceHours', 'ASC');

        /** @var array<int, CategoryRequirement> */
        return $qb->getQuery()->getResult();
    }

    /**
     * 根据证书有效期查找要求
     * @return array<int, CategoryRequirement>
     */
    public function findByValidityPeriod(int $months): array
    {
        return $this->findBy(['certificateValidityPeriod' => $months]);
    }

    /**
     * 获取所有不同的证书有效期
     * @return array<int, array<string, mixed>>
     */
    public function getDistinctValidityPeriods(): array
    {
        $qb = $this->createQueryBuilder('cr');
        
        $qb->select('DISTINCT cr.certificateValidityPeriod')
           ->orderBy('cr.certificateValidityPeriod', 'ASC');

        $result = $qb->getQuery()->getScalarResult();
        
        return array_column($result, 'certificateValidityPeriod');
    }

    /**
     * 统计各种要求的数量
     * @return array<string, mixed>
     */
    public function getRequirementStatistics(): array
    {
        $qb = $this->createQueryBuilder('cr');
        
        $qb->select([
            'COUNT(cr.id) as total',
            'SUM(CASE WHEN cr.requiresPracticalExam = true THEN 1 ELSE 0 END) as practicalExamRequired',
            'SUM(CASE WHEN cr.requiresOnSiteTraining = true THEN 1 ELSE 0 END) as onSiteTrainingRequired',
            'AVG(cr.theoryHours + cr.practiceHours) as avgTotalHours',
            'AVG(cr.certificateValidityPeriod) as avgValidityPeriod'
        ]);

        /** @var array<string, mixed> */
        return $qb->getQuery()->getSingleResult();
    }
} 