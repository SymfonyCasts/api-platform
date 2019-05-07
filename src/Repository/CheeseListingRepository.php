<?php

namespace App\Repository;

use App\Entity\CheeseListing;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Security\Core\Security;

/**
 * @method CheeseListing|null find($id, $lockMode = null, $lockVersion = null)
 * @method CheeseListing|null findOneBy(array $criteria, array $orderBy = null)
 * @method CheeseListing[]    findAll()
 * @method CheeseListing[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CheeseListingRepository extends ServiceEntityRepository
{
    private $security;

    public function __construct(RegistryInterface $registry, Security $security)
    {
        $this->security = $security;
        parent::__construct($registry, CheeseListing::class);
    }

    public function createQueryBuilder($alias, $indexBy = null)
    {
        $querybuilder = parent::createQueryBuilder($alias, $indexBy);
        $user = $this->security->getUser();
        if (!isset($user) || !$this->security->isGranted('ROLE_ADMIN')) {
            $querybuilder->andWhere($alias.'.isPublished = :published')->setParameter('published', true);
        }
        return $querybuilder;
    }

    // /**
    //  * @return CheeseListing[] Returns an array of CheeseListing objects
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
    public function findOneBySomeField($value): ?CheeseListing
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
