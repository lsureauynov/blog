<?php

namespace App\Repository;

use App\Entity\Comments;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Comments>
 *
 * @method Comments|null find($id, $lockMode = null, $lockVersion = null)
 * @method Comments|null findOneBy(array $criteria, array $orderBy = null)
 * @method Comments[]    findAll()
 * @method Comments[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CommentsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Comments::class);
    }

public function findCommentsByUser($userId) : array 
{
    return $this->createQueryBuilder('a')
    ->andWhere('a.user = :userId')
    ->setParameter('userId', $userId)
    ->orderBy('a.id', 'DESC')
    ->getQuery()
    ->getResult();
}

public function findCommentsByArticle($articleId) : array 
{
    return $this->createQueryBuilder('c')
        ->join('c.articles', 'a')
        ->andWhere('a.id = :articleId')
        ->setParameter('articleId', $articleId)
        ->orderBy('c.id', 'DESC')
        ->getQuery()
        ->getResult();

}


}