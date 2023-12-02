<?php

namespace Core\Entity;

use Common\Entity\Girl\Girl;
use Doctrine\Common\Collections\Collection;

interface MetaEntityInterface
{
    public function getClassNameMeta(): string;

    public function getMultiMetaKeys(): array;

    public function getMetaData(): Collection;

    public function setMetaData(Collection $metaData);

    public function addMeta(MetaInterface $meta);

    public function getMeta(string $key): Collection;

    public function getMetaValue(string $key): null|int|array|string;

    public function getMetaValues(string $key): null|array;

}
