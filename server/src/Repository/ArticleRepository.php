<?php

namespace App\Repository;

use App\Entity\Article;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Article>
 */
class ArticleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Article::class);
    }

    public function search(?string $query, ?int $categoryId): QueryBuilder
    {
        $qb = $this->createQueryBuilder('a');

        if ($query) {
            $qb
                ->where("lower(a.title) like CONCAT('%', :query,'%')")
                ->setParameter('query', $query);
        }

        if ($categoryId) {
            $where = $query ? 'andWhere' : 'where';
            $qb
                ->$where("a.category = :categoryId")
                ->setParameter('categoryId', $categoryId);
        }

        return $qb->orderBy('a.createdAt', 'DESC');
    }

    public function exists(string $title): bool 
    {
        return (bool) $this->createQueryBuilder('a')
            ->where('lower(a.title) = :name')
            ->setParameter('name', strtolower($title))
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function save(Article $article): void
    {
        $em = $this->getEntityManager();
        $em->persist($article);
        $em->flush();
    }
}
