<?php

namespace Core\ObjectManager;

use Doctrine\ORM\QueryBuilder;

abstract class UnionQueryAbstract
{
    abstract public function getQuery(QueryBuilder $mainQuery): QueryBuilder;

}
