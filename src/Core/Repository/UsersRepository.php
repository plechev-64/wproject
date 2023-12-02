<?php

namespace Core\Repository;

use Core\Entity\User\User;
use Core\Entity\User\UserMeta;
use Core\Repository\EntityRepositoryAbstract;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Expr\Join;

/**
 * @method User|null findOneBy( array $conditions )
 * @method User|null find( int $id )
 * @method User[] findAllBy( array $conditions )
 */
class UsersRepository extends EntityRepositoryAbstract
{
    public function getEntityClassName(): string
    {
        return User::class;
    }

    public function getCuratorsByCityId(array $cityIds): ArrayCollection
    {

        $result = $this->createQueryBuilder('u')
            ->select('u.id')
            ->addSelect('u.displayName')
            ->addSelect('umCity.value as city')
            ->join(UserMeta::class, 'umCity', Join::WITH, 'u.id=umCity.userId')
            ->join(UserMeta::class, 'umCurator', Join::WITH, 'u.id=umCurator.userId')
            ->where('umCity.key=\'user_city\'')
            ->andWhere('umCity.value IN (:ids)')
            ->andWhere('umCurator.key=\'wp_capabilities\'')
            ->andWhere('umCurator.value LIKE \'%curator%\'')
            ->setParameter('ids', $cityIds)
            ->orderBy('u.displayName', 'ASC')
            ->getQuery()
            ->getResult();

        return new ArrayCollection($result);

    }

}
