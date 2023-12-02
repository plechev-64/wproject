<?php

namespace Core\Repository;

use Core\ORM;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadata;

abstract class EntityRepositoryAbstract extends EntityRepository
{
    abstract public function getEntityClassName(): string;

    public function __construct()
    {
        parent::__construct(ORM::get()->getManager(), new ClassMetadata(
            $this->getEntityClassName()
        ));
    }

}
