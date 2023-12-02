<?php

namespace Core\ObjectManager;

use Core\Entity\Post\Post;
use Core\ORM;
use Doctrine\ORM\QueryBuilder;

class PostManager extends ObjectManagerAbstract
{
    protected int $number = 48;

    protected function getEntityClassName(): string
    {
        return Post::class;
    }

    protected function getAlias(): string
    {
        return 'posts';
    }

    protected function getRoute(): string
    {
        return '/post/list';
    }

    protected function getMainQuery(): QueryBuilder
    {
        return ORM::get()
                    ->getRepository($this->getEntityClassName())
                    ->createQueryBuilder($this->getAlias());
    }

    protected function getFilterRules(): array
    {
        return [
            'post_types'              => function (QueryBuilder $query, $value) {
                if (!is_array($value)) {
                    return $query;
                }
                return $query
                    ->andWhere($this->getAlias().'.postType IN (:postTypes)')
                    ->setParameter('postTypes', $value);
            },
        ];
    }

}
