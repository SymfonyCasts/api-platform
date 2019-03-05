<?php
namespace App\Repository;
use App\Entity\CheeseType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
/**
 * @method CheeseType|null find($id, $lockMode = null, $lockVersion = null)
 * @method CheeseType|null findOneBy(array $criteria, array $orderBy = null)
 * @method CheeseType[]    findAll()
 * @method CheeseType[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CheeseTypeRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, CheeseType::class);
    }
    // /**
    //  * @return CheeseType[] Returns an array of CheeseType objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */
    /*
    public function findOneBySomeField($value): ?CheeseType
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
