<?php

namespace Core\Entity;

interface MetaInterface
{
    public function setEntity(MetaEntityInterface $entity);
    public function getEntity(): MetaEntityInterface;
    public function getKey(): string;
    public function setKey(string $key): MetaInterface;
    public function getValue(): string|array|null|int;
    public function setValue(string|array|null|int $value): MetaInterface;
}
