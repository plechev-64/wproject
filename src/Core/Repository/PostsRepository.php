<?php

namespace Core\Repository;

use Core\Entity\Post\Post;
use Core\Repository\EntityRepositoryAbstract;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @method Post|null findOneBy( array $conditions )
 * @method Post|null find( int $id )
 * @method Post[] findAllBy( array $conditions )
 */
class PostsRepository extends EntityRepositoryAbstract
{
    public function getEntityClassName(): string
    {
        return Post::class;
    }

    public function getPostsByIds(array $ids): ArrayCollection
    {

        return new ArrayCollection(
            $this
                ->createQueryBuilder('s')
                ->andWhere('s.id IN (:ids)')
                ->setParameter('ids', $ids)
                ->getQuery()
                ->getResult()
        );

    }

}
