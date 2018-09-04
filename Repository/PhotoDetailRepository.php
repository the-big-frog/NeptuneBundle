<?php

namespace ScyLabs\NeptuneBundle\Repository;

use ScyLabs\NeptuneBundle\Entity\PhotoDetail;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method PhotoDetail|null find($id, $lockMode = null, $lockVersion = null)
 * @method PhotoDetail|null findOneBy(array $criteria, array $orderBy = null)
 * @method PhotoDetail[]    findAll()
 * @method PhotoDetail[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PhotoDetailRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, PhotoDetail::class);
    }

//    /**
//     * @return PhotoDetail[] Returns an array of PhotoDetail objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('z')
            ->andWhere('z.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('z.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?PhotoDetail
    {
        return $this->createQueryBuilder('z')
            ->andWhere('z.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
