<?php

namespace Core\Repository;

use Core\Entity\Taxonomy\Term;
use Core\Repository\EntityRepositoryAbstract;

/**
 * @method Term|null findOneBy( array $conditions )
 * @method Term|null find( int $id )
 * @method Term[] findAllBy( array $conditions )
 */
class TermsRepository extends EntityRepositoryAbstract
{
    public function getEntityClassName(): string
    {
        return Term::class;
    }

    public function getTermBySlug(string $slug): ?Term
    {

        return $this->createQueryBuilder('t')
            ->andWhere('t.slug = :slug')
            ->setParameter('slug', $slug)
            ->getQuery()
            ->getOneOrNullResult();

    }

}
