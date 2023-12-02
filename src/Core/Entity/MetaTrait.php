<?php

namespace Core\Entity;

use Common\Entity\Girl\Girl;
use Core\Service\StringService;
use Doctrine\Common\Collections\Collection;

trait MetaTrait
{
    private Collection $metaData;

    abstract public function getClassNameMeta(): string;

    public function getMultiMetaKeys(): array
    {
        return [];
    }

    /**
     * @return Collection
     */
    public function getMetaData(): Collection
    {
        return $this->metaData;
    }

    /**
     * @param Collection $metaData
     *
     * @return self
     */
    public function setMetaData(Collection $metaData): self
    {
        $this->metaData = $metaData;

        return $this;
    }

    /**
     * @param MetaInterface $meta
     *
     * @return MetaTrait
     */
    public function addMeta(MetaInterface $meta): self
    {
        if(!$this->metaData->contains($meta)) {
            $this->metaData->add($meta);
            $meta->setEntity($this);
        }

        return $this;
    }

    /**
     * @param string $key
     *
     * @return Collection
     */
    public function getMeta(string $key): Collection
    {
        return $this->metaData->filter(function (MetaInterface $meta) use ($key) {
            return $meta->getKey() === $key;
        });
    }

    public function getMetaValue(string $key): null|int|array|string
    {
        $metaCollection = $this->getMeta($key);

        if(!$metaCollection->count()) {
            return null;
        }

        /** @var MetaInterface $meta */
        $meta = $metaCollection->first();

        $value = StringService::maybeUnserialize($meta->getValue());

        return wp_unslash($value);

    }

    public function getMetaValues(string $key): null|array
    {
        $metaCollection = $this->getMeta($key);

        if(!$metaCollection->count()) {
            return null;
        }

        $values = [];
        /** @var MetaInterface $meta */
        foreach($metaCollection as $meta) {
            $values[] = $meta->getValue();
        }

        return $values;
    }

}
